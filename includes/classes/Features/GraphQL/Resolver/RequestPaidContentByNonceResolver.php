<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Post\ContentFilter;
use Cornix\Serendipity\Core\Lib\Post\PostContent;
use Cornix\Serendipity\Core\Lib\Repository\Confirmations;
use Cornix\Serendipity\Core\Lib\Repository\Invoice;
use Cornix\Serendipity\Core\Lib\Repository\InvoiceNonce;
use Cornix\Serendipity\Core\Lib\Repository\RpcURL;
use Cornix\Serendipity\Core\Lib\Repository\ServerSignerData;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\AppClientFactory;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClientFactory;
use Cornix\Serendipity\Core\Types\BlockNumberType;
use Cornix\Serendipity\Core\Types\InvoiceIdType;

class RequestPaidContentByNonceResolver extends ResolverBase {

	// ここの定数は、GraphQLのエラーコードと一致させること
	private const ERROR_CODE_INVALID_NONCE           = 'INVALID_NONCE';
	private const ERROR_CODE_INVALID_CHAIN_ID        = 'INVALID_CHAIN_ID';
	private const ERROR_CODE_PAYWALL_LOCKED          = 'PAYWALL_LOCKED';
	private const ERROR_CODE_TRANSACTION_UNCONFIRMED = 'TRANSACTION_UNCONFIRMED';

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var string */
		$invoice_ID_hex = $args['invoiceID'];
		/** @var string */
		$nonce = $args['nonce'];

		Judge::checkHex( $invoice_ID_hex );
		$invoice_ID = InvoiceIdType::from( $invoice_ID_hex );

		// エラー時の結果を返すコールバック関数
		$error_result_callback = fn( $error_code ) => array(
			'content'   => null,
			'errorCode' => $error_code,
		);

		global $wpdb;
		if ( ! ( new InvoiceNonce( $wpdb ) )->exists( $invoice_ID, $nonce ) ) {
			// nonceが無効な場合はドメインエラーとして返す
			return $error_result_callback( self::ERROR_CODE_INVALID_NONCE );
		}

		$invoice_data = ( new Invoice( $wpdb ) )->get( $invoice_ID );
		if ( is_null( $invoice_data ) ) {
			// 通常、ここは通らない
			throw new \Exception( '[D2AAA3B6] Invoice data not found. invoiceID: ' . $invoice_ID_hex );
		}
		$post_ID          = $invoice_data->postID();
		$chain_ID         = $invoice_data->chainID();
		$consumer_address = $invoice_data->consumerAddress();

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		if ( ! ( new RpcUrl() )->isRegistered( $chain_ID ) ) {
			// 指定されたチェーンIDが接続可能でない場合はドメインエラーとして返す
			// ※ 支払い後、管理者によってチェーンが無効化された場合はここを通るため、例外を投げない
			return $error_result_callback( self::ERROR_CODE_INVALID_CHAIN_ID );
		}

		// ブロックチェーンに問い合わせる
		$app                   = ( new AppClientFactory() )->create( $chain_ID );
		$server_signer_address = ( new ServerSignerData() )->getAddress();
		$payment_status        = $app->getPaywallStatus( $server_signer_address, $post_ID, $consumer_address );

		if ( ! $payment_status->isUnlocked() ) {
			// 最新のブロックでもペイウォールの解除が確認できなかった場合
			return $error_result_callback( self::ERROR_CODE_PAYWALL_LOCKED );
		} elseif ( ! $this->isConfirmed( $chain_ID, $payment_status->unlockedBlockNumber() ) ) {
			// 最新のブロックではペイウォールの解除が確認できたが、
			// トランザクションの待機ブロック数が管理者が指定した数を下回っている場合
			return $error_result_callback( self::ERROR_CODE_TRANSACTION_UNCONFIRMED );
		}

		// 投稿の有料部分を取得
		// HTMLコメントを含まない投稿本文を取得
		$content = ( new PostContent( $post_ID ) )->getCommentRemoved();

		// 有料部分のコンテンツを取得
		$paid_content = ( new ContentFilter( $content ) )->getPaid();

		return array(
			'content'   => $paid_content,
			'errorCode' => null,
		);
	}

	/**
	 * トランザクションが待機済みかどうかを判定します。
	 */
	private function isConfirmed( int $chain_ID, BlockNumberType $unlocked_block_number ): bool {
		// トランザクションの待機ブロック数を取得
		$confirmations = ( new Confirmations() )->get( $chain_ID );

		if ( is_int( $confirmations ) ) {
			// 最新のブロック番号を取得
			$latest_block_number = ( new BlockchainClientFactory() )->create( $chain_ID )->getBlockNumber();
			// 基準となるブロック番号を計算(「ペイウォール解除時のブロック番号」<=「基準ブロック番号」となる場合、待機済み)
			$reference_block = $latest_block_number->sub( max( $confirmations - 1, 0 ) );
			return $unlocked_block_number->compare( $reference_block ) <= 0;
		} elseif ( $confirmations === 'latest' ) {
			// ペイウォールが解除されたブロック番号が取得できているため、当然待機済みと判定
			return true;
		} elseif ( $confirmations === 'finalized' ) {
			// TODO: 未実装
			throw new \Exception( '[8A320100] Finalized block number is not implemented yet.' );
		} else {
			throw new \Exception( '[2251BA42] Invalid confirmations value. confirmations: ' . var_export( $confirmations, true ) );
		}
	}
}
