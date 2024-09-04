<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes;

use Cornix\Serendipity\Core\Lib\Post\PostContent;
use Cornix\Serendipity\Core\Lib\Repository\BlockName;
use Cornix\Serendipity\Core\Types\PriceType;
use Cornix\Serendipity\Core\Types\WidgetAttributesType;

class WidgetAttributes {
	public function __construct( PostContent $post_content ) {
		$this->post_content = $post_content;
	}

	private PostContent $post_content;

	/**
	 * ウィジェットの属性を取得します。
	 */
	public function get(): ?WidgetAttributesType {
		// ウィジェットのブロック情報を取得
		$widget_parser_block = $this->getWidgetParserBlock();

		// ウィジェットの属性の型に変換して返す
		return is_null( $widget_parser_block ) ? null : $this->convertToWidgetAttributesType( $widget_parser_block );
	}

	/**
	 * 取得したブロックの情報をWidgetAttributesTypeに変換します。
	 */
	private function convertToWidgetAttributesType( array $widget_parser_block ): WidgetAttributesType {
		$attributes = $widget_parser_block['attrs'];
		if ( $attributes === null ) {
			return null;
		}

		// 以下のキーが存在することを確認
		assert( array_key_exists( 'sellingNetworkCategory', $attributes ), '[A2D17053] sellingNetworkCategory property does not exist' );
		assert( array_key_exists( 'sellingPrice', $attributes ), '[65A44855] sellingPrice property does not exist' );
		assert( array_key_exists( 'amountHex', $attributes['sellingPrice'] ), '[2018DA62] amountHex property does not exist' );
		assert( array_key_exists( 'decimals', $attributes['sellingPrice'] ), '[CC49D23A] decimals property does not exist' );

		// 保存された販売ネットワークを取得
		$selling_network = $attributes['sellingNetworkCategory'];
		// 保存された価格を取得
		/** @var array{amountHex:string,decimals:int,symbol:?string} */
		$selling_price = $attributes['sellingPrice'];

		// 価格をPriceTypeに変換
		$price = new PriceType( $selling_price['amountHex'], $selling_price['decimals'], $selling_price['symbol'] );

		return new WidgetAttributesType( $price, $selling_network );
	}

	/**
	 * ウィジェットのブロック情報を取得します。
	 */
	private function getWidgetParserBlock(): ?array {
		$post_content = $this->post_content->get();
		$blocks       = parse_blocks( $post_content );
		$block_name   = BlockName::get(); // ウィジェットに付与されているブロック名

		// `blockName`プロパティが$block_nameと一致するブロックを取得
		$blocks = array_filter(
			$blocks,
			function ( $block ) use ( $block_name ) {
				return $block_name === $block['blockName'];
			}
		);
		// ウィジェットは1投稿につき1つまでしか存在しない
		assert( count( $blocks ) <= 1, '[FD104DDE] Widget block must be only one in a post' );

		// ウィジェットが存在しない場合はnullを返す
		return 0 === count( $blocks ) ? null : $blocks[0];
	}
}
