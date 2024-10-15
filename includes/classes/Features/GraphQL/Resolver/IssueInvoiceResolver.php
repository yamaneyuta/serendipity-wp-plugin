<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\Invoice;
use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\WidgetAttributes;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\Token;

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

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );
		// 指定されたトークンアドレスが支払可能な設定になっているかどうかをチェック
		$token = Token::from( $chain_ID, $token_address );
		Judge::checkPayableToken( $token );

		// 投稿設定を取得
		$widget_attributes = WidgetAttributes::fromPostID( $post_ID );
		if ( null === $widget_attributes ) {
			throw new \Exception( '[6BDB4DC3] WidgetAttributes not found' );
		}

		// 現時点での販売価格を取得
		$selling_price = $widget_attributes->sellingPrice();

		// ここからテスト用コード -->
		// 暫定でトークンの数量を決定
		$payment_amount_hex = '0x' . dechex( 1000000000000000000 );   // 1ETH
		// <-- ここまでテスト用コード

		// 請求書番号を発行
		global $wpdb;
		$invoice_id = ( new Invoice( $wpdb ) )->issue( $selling_price );

		return array(
			'invoiceIdHex'     => $invoice_id->hex(),
			'seller'           => $root_value['seller']( $root_value, array() ),
			'paymentToken'     => $root_value['token'](
				$root_value,
				array(
					'chainID' => $token->chainID(),
					'address' => $token->address(),
				)
			),
			'paymentAmountHex' => $payment_amount_hex,
		);
	}
}
