<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Post\PostContent;
use Cornix\Serendipity\Core\Lib\Repository\PurchaseTicket;
use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\WidgetAttributes;

class IssuePurchaseTicketResolver extends ResolverBase {

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
		$purchase_symbol = $args['purchaseSymbol'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// TODO: チェーンIDは購入可能であることをチェック
		// TODO: 購入シンボルが有効であることをチェック

		// 投稿設定を取得
		$widget_attributes = ( new WidgetAttributes( new PostContent( $post_ID ) ) )->get();
		if ( null === $widget_attributes ) {
			throw new \Exception( '[6BDB4DC3] WidgetAttributes not found' );
		}

		// 現時点での販売価格を取得
		$selling_amount_hex = $widget_attributes->sellingAmountHex;
		$selling_decimals   = $widget_attributes->sellingDecimals;
		$selling_symbol     = $widget_attributes->sellingSymbol;

		global $wpdb;
		$purchase_ticket_id = ( new PurchaseTicket( $wpdb ) )->issue( $selling_amount_hex, $selling_decimals, $selling_symbol );

		echo 'purchase_ticket_id: ' . var_export( $purchase_ticket_id, true ) . PHP_EOL;
		exit;

		return array(
			// TODO: 実装
			'ticketIdHex' => $purchase_ticket_id,
		);
	}
}
