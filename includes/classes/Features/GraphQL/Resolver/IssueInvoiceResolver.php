<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Calc\PriceExchange;
use Cornix\Serendipity\Core\Lib\Calc\SolidityStrings;
use Cornix\Serendipity\Core\Service\InvoiceService;
use Cornix\Serendipity\Core\Repository\BlockNumberActiveSince;
use Cornix\Serendipity\Core\Repository\ConsumerTerms;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClientFactory;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Repository\TokenRepository;
use Cornix\Serendipity\Core\Service\Factory\ServerSignerServiceFactory;
use Cornix\Serendipity\Core\Service\Factory\TermsServiceFactory;
use Cornix\Serendipity\Core\Service\PostService;
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
		$token_address    = Address::from( $args['tokenAddress'] ?? null );
		$consumer_address = Address::from( $args['consumerAddress'] ?? null ); // 購入者のアドレス

		if ( null === $token_address ) {
			throw new \InvalidArgumentException( '[[5D2F1CF4] Token address must not be null.' );
		} elseif ( null === $consumer_address ) {
			throw new \InvalidArgumentException( '[3AF96606] Consumer address must not be null.' );
		}

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );
		// 指定されたトークンアドレスが支払可能な設定になっているかどうかをチェック
		$token = ( new TokenRepository() )->get( $chain_ID, $token_address );
		if ( null === $token || ! $token->isPayable() ) {
			throw new \InvalidArgumentException( '[4381A464] The specified token is not payable.' );
		}

		// 販売者情報を取得
		$seller_singed_terms = ( new TermsServiceFactory() )->create()->getSignedSellerTerms();
		assert( $seller_singed_terms, '[88C95394] SellerAgreedTerms not found' );
		$seller_address = Ethers::verifyMessage( $seller_singed_terms->terms()->message(), $seller_singed_terms->signature() );

		// 販売価格を取得
		$selling_price = ( new PostService() )->get( $post_ID )->sellingPrice();
		assert( ! is_null( $selling_price ), '[F8524488] Selling price is null for post ID: ' . $post_ID );

		// 支払うトークンにおける価格を計算
		// ※ これは`1ETH`等の価格を表現するオブジェクトであり、実際に支払う数量(wei等)ではないことに注意
		$payment_price = ( new PriceExchange() )->convert( $selling_price, $token->symbol() );
		// 支払うトークン量を取得
		$payment_amount_hex = $payment_price->toTokenAmount( $chain_ID );

		// 請求書番号を発行(+現在の販売価格を記録)
		global $wpdb;
		try {
			$wpdb->query( 'START TRANSACTION' );
			$invoice = ( new InvoiceService() )->issue( $post_ID, $chain_ID, $selling_price, $seller_address, $token_address, $payment_amount_hex, $consumer_address );
			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		// 署名用ウォレットで署名を行うためのメッセージを作成
		$server_message = SolidityStrings::valueToHexString( $chain_ID->value() )
			. SolidityStrings::addressToHexString( $seller_address )
			. SolidityStrings::addressToHexString( $consumer_address )
			. SolidityStrings::valueToHexString( $invoice->id()->hex() )
			. SolidityStrings::valueToHexString( $post_ID )
			. SolidityStrings::addressToHexString( $token_address )
			. SolidityStrings::valueToHexString( $payment_amount_hex )
			. SolidityStrings::valueToHexString( ( new ConsumerTerms() )->currentVersion() )
			. SolidityStrings::addressToHexString( Ethers::zeroAddress() )    // TODO: アフィリエイターのアドレス
			. SolidityStrings::valueToHexString( 0 );  // TODO: アフィリエイト報酬率
		// サーバーの署名用ウォレットで署名
		global $wpdb;
		$server_signer    = ( new ServerSignerServiceFactory() )->create( $wpdb )->getServerSigner();
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
			'paymentAmountHex' => $payment_amount_hex,
		);
	}
}
