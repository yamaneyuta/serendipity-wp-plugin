<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Calc\PriceExchange;
use Cornix\Serendipity\Core\Lib\Calc\SolidityStrings;
use Cornix\Serendipity\Core\Lib\Repository\BlockNumberActiveSince;
use Cornix\Serendipity\Core\Lib\Repository\ConsumerTerms;
use Cornix\Serendipity\Core\Lib\Repository\Invoice;
use Cornix\Serendipity\Core\Lib\Repository\InvoiceNonce;
use Cornix\Serendipity\Core\Lib\Repository\SellerAgreedTerms;
use Cornix\Serendipity\Core\Lib\Repository\ServerSignerData;
use Cornix\Serendipity\Core\Lib\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClientFactory;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\Signer;
use Cornix\Serendipity\Core\Types\TokenType;

class IssueInvoiceResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];
		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var string */
		$token_address = $args['tokenAddress'];
		/** @var string */
		$consumer_address = $args['consumerAddress']; // 購入者のアドレス

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );
		// 指定されたトークンアドレスが支払可能な設定になっているかどうかをチェック
		$token = ( new TokenData() )->get( $chain_ID, $token_address );
		Judge::checkPayableToken( $token );

		// 販売者情報を取得
		$seller_agreed_terms = new SellerAgreedTerms();
		if ( ! $seller_agreed_terms->exists() ) {
			throw new \Exception( '[88C95394] SellerAgreedTerms not found' );
		}
		$seller_address = Ethers::verifyMessage( $seller_agreed_terms->message(), $seller_agreed_terms->signature() );

		// 投稿設定を取得
		$widget_attributes = WidgetAttributes::fromPostID( $post_ID );
		if ( null === $widget_attributes ) {
			throw new \Exception( '[6BDB4DC3] WidgetAttributes not found' );
		}

		// 現時点での販売価格を取得
		$selling_price = $widget_attributes->sellingPrice();

		// 支払うトークンにおける価格を計算
		// ※ これは`1ETH`等の価格を表現するオブジェクトであり、実際に支払う数量(wei等)ではないことに注意
		$payment_price = ( new PriceExchange() )->convert( $selling_price, $token->symbol() );
		// 支払うトークン量を取得
		$payment_amount_hex = $payment_price->toTokenAmount( $chain_ID );

		// 請求書番号を発行(+現在の販売価格を記録)
		global $wpdb;
		$invoice_id = ( new Invoice( $wpdb ) )->issue( $post_ID, $chain_ID, $selling_price, $seller_address, $consumer_address );
		$nonce      = ( new InvoiceNonce( $wpdb ) )->new( $invoice_id );

		// 署名用ウォレットで署名を行うためのメッセージを作成
		$server_message = SolidityStrings::valueToHexString( $chain_ID )
			. SolidityStrings::addressToHexString( $seller_address )
			. SolidityStrings::addressToHexString( $consumer_address )
			. SolidityStrings::valueToHexString( $invoice_id->hex() )
			. SolidityStrings::valueToHexString( $post_ID )
			. SolidityStrings::addressToHexString( $token_address )
			. SolidityStrings::valueToHexString( $payment_amount_hex )
			. SolidityStrings::valueToHexString( ( new ConsumerTerms() )->currentVersion() )
			. SolidityStrings::addressToHexString( Ethers::zeroAddress() )    // TODO: アフィリエイターのアドレス
			. SolidityStrings::valueToHexString( 0 );  // TODO: アフィリエイト報酬率
		// サーバーの署名用ウォレットで署名
		$server_signer    = new Signer( ( new ServerSignerData() )->getPrivateKey() );
		$server_signature = $server_signer->signMessage( $server_message );

		// 最後に、有効になったブロック番号が設定されていない場合は設定
		if ( is_null( ( new BlockNumberActiveSince() )->get( $chain_ID ) ) ) {
			$blockchain_client = ( new BlockchainClientFactory() )->create( $chain_ID );
			$block_number      = $blockchain_client->getBlockNumber(); // 現在の最新ブロック番号
			( new BlockNumberActiveSince() )->set( $chain_ID, $block_number );
		}

		return array(
			'invoiceIdHex'     => $invoice_id->hex(),
			'nonce'            => $nonce,
			'serverMessage'    => $server_message,
			'serverSignature'  => $server_signature,
			'paymentAmountHex' => $payment_amount_hex,
		);
	}
}
