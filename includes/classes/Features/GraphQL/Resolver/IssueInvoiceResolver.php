<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Calc\SolidityStrings;
use Cornix\Serendipity\Core\Repository\BlockNumberActiveSince;
use Cornix\Serendipity\Core\Repository\ConsumerTerms;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClientFactory;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Application\Factory\ServerSignerServiceFactory;
use Cornix\Serendipity\Core\Application\UseCase\IssueInvoice;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class IssueInvoiceResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID          = $args['postID'];
		$chain_ID         = new ChainID( $args['chainID'] );
		$token_address    = new Address( $args['tokenAddress'] );
		$consumer_address = new Address( $args['consumerAddress'] ); // 購入者のアドレス

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// 請求書番号を発行(+現在の販売価格を記録)
		global $wpdb;
		try {
			$wpdb->query( 'START TRANSACTION' );
			$invoice = ( new IssueInvoice( $wpdb ) )->handle( $post_ID, $chain_ID, $token_address, $consumer_address );
			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		// 署名用ウォレットで署名を行うためのメッセージを作成
		$server_message = SolidityStrings::valueToHexString( $chain_ID->value() )
			. SolidityStrings::addressToHexString( $invoice->sellerAddress() )
			. SolidityStrings::addressToHexString( $invoice->consumerAddress() )
			. SolidityStrings::valueToHexString( $invoice->id()->hex() )
			. SolidityStrings::valueToHexString( $invoice->postID() )
			. SolidityStrings::addressToHexString( $invoice->paymentTokenAddress() )
			. SolidityStrings::valueToHexString( $invoice->paymentAmountHex() )
			. SolidityStrings::valueToHexString( ( new ConsumerTerms() )->currentVersion() )
			. SolidityStrings::addressToHexString( Ethers::zeroAddress() )    // TODO: アフィリエイターのアドレス
			. SolidityStrings::valueToHexString( 0 );  // TODO: アフィリエイト報酬率
		// サーバーの署名用ウォレットで署名
		$server_signer    = ( new ServerSignerServiceFactory() )->create()->getServerSigner();
		$server_signature = $server_signer->signMessage( $server_message );

		// 最後に、有効になったブロック番号が設定されていない場合は設定
		if ( is_null( ( new BlockNumberActiveSince() )->get( $chain_ID ) ) ) {
			$blockchain_client = ( new BlockchainClientFactory() )->create( $chain_ID );
			$block_number      = $blockchain_client->getBlockNumber(); // 現在の最新ブロック番号
			( new BlockNumberActiveSince() )->set( $chain_ID, $block_number );
		}

		return array(
			'invoiceIdHex'     => $invoice->id()->hex(),
			'nonce'            => $invoice->nonce()->value(),
			'serverMessage'    => $server_message,
			'serverSignature'  => $server_signature,
			'paymentAmountHex' => $invoice->paymentAmountHex(),
		);
	}
}
