<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Calc\PriceExchange;
use Cornix\Serendipity\Core\Lib\Repository\Invoice;
use Cornix\Serendipity\Core\Lib\Repository\InvoiceNonce;
use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes;
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

		// 支払うトークンにおける価格を計算
		// ※ これは`1ETH`等の価格を表現するオブジェクトであり、実際に支払う数量(wei等)ではないことに注意
		$payment_price = ( new PriceExchange() )->convert( $selling_price, $token->symbol() );

		// 請求書番号を発行(+現在の販売価格を記録)
		global $wpdb;
		$invoice_id = ( new Invoice( $wpdb ) )->issue( $selling_price );
		$nonce      = ( new InvoiceNonce( $wpdb ) )->new( $invoice_id );

		return array(
			'invoiceIdHex'     => $invoice_id->hex(),
			'nonce'            => $nonce,
			'seller'           => $root_value['seller']( $root_value, array() ),
			'paymentToken'     => $root_value['token'](
				$root_value,
				array(
					'chainID' => $token->chainID(),
					'address' => $token->address(),
				)
			),
			'paymentAmountHex' => $payment_price->toTokenAmount( $chain_ID ),
		);
	}
}
