<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Infrastructure\Web3\AppContractClient;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClientFactory;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Application\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Application\Factory\ServerSignerServiceFactory;
use Cornix\Serendipity\Core\Application\Service\PostService;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;

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

		Validate::checkHex( $invoice_ID_hex );
		Validate::checkInvoiceNonceValueFormat( $nonce );
		$invoice_ID = InvoiceID::from( $invoice_ID_hex );

		// エラー時の結果を返すコールバック関数
		$error_result_callback = fn( $error_code ) => array(
			'content'   => null,
			'errorCode' => $error_code,
		);

		$invoice = ( new InvoiceRepository() )->get( $invoice_ID );
		if ( is_null( $invoice ) ) {
			// 通常、ここは通らない
			throw new \Exception( '[D2AAA3B6] Invoice data not found. invoiceID: ' . $invoice_ID_hex );
		}

		$db_nonce = $invoice->nonce(); // DBから取得したnonce
		if ( is_null( $db_nonce ) || $nonce !== $db_nonce->value() ) {
			// nonceが無効な場合はドメインエラーとして返す
			return $error_result_callback( self::ERROR_CODE_INVALID_NONCE );
		}

		$post_ID          = $invoice->postID();
		$chain            = ( new ChainServiceFactory() )->create( $GLOBALS['wpdb'] )->getChain( $invoice->chainID() );
		$consumer_address = $invoice->consumerAddress();

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		if ( ! $chain->connectable() ) {
			// 指定されたチェーンIDが接続可能でない場合はドメインエラーとして返す
			// ※ 支払い後、管理者によってチェーンが無効化された場合はここを通るため、例外を投げない
			return $error_result_callback( self::ERROR_CODE_INVALID_CHAIN_ID );
		}

		// ブロックチェーンに問い合わせる
		$app_contract   = ( new AppContractRepository() )->get( $chain->id() );
		$app            = new AppContractClient( $app_contract );
		$server_signer  = ( new ServerSignerServiceFactory() )->create( $GLOBALS['wpdb'] )->getServerSigner();
		$payment_status = $app->getPaywallStatus( $server_signer->address(), $post_ID, $consumer_address );

		if ( ! $payment_status->isUnlocked() ) {
			// 最新のブロックでもペイウォールの解除が確認できなかった場合
			return $error_result_callback( self::ERROR_CODE_PAYWALL_LOCKED );
		} elseif ( ! $this->isConfirmed( $chain->id(), $payment_status->unlockedBlockNumber() ) ) {
			// 最新のブロックではペイウォールの解除が確認できたが、
			// トランザクションの待機ブロック数が管理者が指定した数を下回っている場合
			return $error_result_callback( self::ERROR_CODE_TRANSACTION_UNCONFIRMED );
		}

		// 有料部分のコンテンツを取得
		$paid_content = ( new PostService() )->get( $post_ID )->paidContent();
		assert( ! is_null( $paid_content ), '[391C0A77] Paid content should not be null.' );

		return array(
			'content'   => apply_filters( 'the_content', $paid_content->value() ),
			'errorCode' => null,
		);
	}

	/**
	 * トランザクションが待機済みかどうかを判定します。
	 */
	private function isConfirmed( ChainID $chain_ID, BlockNumber $unlocked_block_number ): bool {
		// トランザクションの待機ブロック数を取得
		$chain         = ( new ChainServiceFactory() )->create( $GLOBALS['wpdb'] )->getChain( $chain_ID );
		$confirmations = $chain->confirmations();

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
