<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\PurchaseTicket;
use Cornix\Serendipity\Core\Lib\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\WidgetAttributes;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\Signer;

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
		$payment_symbol = $args['paymentSymbol'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// 支払おうとしているチェーンIDが有効であることをチェック
		Judge::checkPayableChainID( $chain_ID );
		// 支払おうとしているトークンが有効であることをチェック
		Judge::checkPayableSymbol( $chain_ID, $payment_symbol );

		// 支払用のトークンのコントラクトアドレスを取得
		$token_data            = new TokenData();
		$payment_token_address = $token_data->getAddress( $chain_ID, $payment_symbol );
		if ( null === $payment_token_address ) {
			throw new \Exception( '[BDF1883A] Token address not found' );
		}

		// 投稿設定を取得
		$widget_attributes = WidgetAttributes::fromPostID( $post_ID );
		if ( null === $widget_attributes ) {
			throw new \Exception( '[6BDB4DC3] WidgetAttributes not found' );
		}

		// 現時点での販売価格を取得
		$selling_price = $widget_attributes->sellingPrice();

		// 購入用のチケットを発行
		global $wpdb;
		$purchase_ticket_id = ( new PurchaseTicket( $wpdb ) )->issue( $selling_price );

		// 販売者の利用規約同意時の署名を取得
		// $seller_terms_agreements = new AgreedSellerTerms();
		// TODO: テスト用にここで署名を作成
		// $seller_terms_agreement_info = (new Terms())->sellerAgreedMessageInfo();
		// ここからテスト用コード -->
		// テスト用ウォレットの秘密鍵(mnemonic: "test test test test test test test test test test test junk")
		// -> ウォレットアドレス: 0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266
		$test_private_key               = 'ac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80';
		$seller_terms_agreement_message = 'I agree to the seller\'s terms of service v1';
		$seller_signature               = ( new Signer( $test_private_key ) )->signMessage( $seller_terms_agreement_message );
		$seller_signature_version       = 1;  // TODO: 削除
		// 暫定でETHでの購入とする
		if ( 1 !== $chain_ID ) {
			throw new \Exception( '[155C67AE] Chain ID is not supported' );
		}
		$payment_amount_hex = '0x' . dechex( 1000000000000000000 );   // 1ETH
		// <-- ここまでテスト用コード

		return array(
			'purchaseTicketIdHex'    => '0x' . str_replace( '-', '', $purchase_ticket_id ),
			'sellerSignature'        => $seller_signature,
			'sellerSignatureVersion' => $seller_signature_version,
			'paymentTokenAddress'    => $payment_token_address,
			'paymentAmountHex'       => $payment_amount_hex,
		);
	}
}
