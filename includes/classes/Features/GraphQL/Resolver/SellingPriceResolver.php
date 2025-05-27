<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Database\Schema\PaidContentTable;

class SellingPriceResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// 販売価格をテーブルから取得して返す
		$selling_price = ( new PaidContentTable() )->getSellingPrice( $post_ID );
		return is_null( $selling_price ) ? null : array(
			'amountHex' => $selling_price->amountHex(),
			'decimals'  => $selling_price->decimals(),
			'symbol'    => $selling_price->symbol(),
		);
	}
}
