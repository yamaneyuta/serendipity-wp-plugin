<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes;

use Cornix\Serendipity\Core\Lib\Post\PostContent;
use Cornix\Serendipity\Core\Lib\Repository\BlockName;
use Cornix\Serendipity\Core\Types\NetworkCategory;
use WP_Block_Parser_Block;

class WidgetAttributes {
	private const ATTRS_KEY_SELLING_NETWORK_CATEGORY_ID = 'sellingNetworkCategoryID';
	private const ATTRS_KEY_SELLING_AMOUNT_HEX          = 'sellingAmountHex';
	private const ATTRS_KEY_SELLING_DECIMALS            = 'sellingDecimals';
	private const ATTRS_KEY_SELLING_SYMBOL              = 'sellingSymbol';

	public function __construct( array $attrs ) {
		$this->attrs = $attrs;
	}

	public static function from( NetworkCategory $network_category, string $amount_hex, int $decimals, string $symbol ): WidgetAttributes {
		return new self(
			array(
				self::ATTRS_KEY_SELLING_NETWORK_CATEGORY_ID => $network_category->id(),
				self::ATTRS_KEY_SELLING_AMOUNT_HEX => $amount_hex,
				self::ATTRS_KEY_SELLING_DECIMALS   => $decimals,
				self::ATTRS_KEY_SELLING_SYMBOL     => $symbol,
			)
		);
	}

	public static function fromPostID( int $post_ID ): ?WidgetAttributes {
		$attrs = ( new WidgetParser() )->attrs( $post_ID );

		return is_null( $attrs ) ? null : new self( $attrs );
	}

	private array $attrs;

	public function toArray(): array {
		return $this->attrs;
	}


	/** 販売対象のネットワークカテゴリを取得します。 */
	public function sellingNetworkCategory(): ?NetworkCategory {
		$selling_network_category_id = $this->attrs[ self::ATTRS_KEY_SELLING_NETWORK_CATEGORY_ID ] ?? null;
		return is_null( $selling_network_category_id ) ? null : NetworkCategory::from( $selling_network_category_id );
	}

	/** 販売価格の値(sellingDecimalsの値と共に使用する)を取得します。 */
	public function sellingAmountHex(): ?string {
		return $this->attrs[ self::ATTRS_KEY_SELLING_AMOUNT_HEX ] ?? null;
	}


	/** 販売価格の小数点以下桁数を取得します。 */
	public function sellingDecimals(): ?int {
		return $this->attrs[ self::ATTRS_KEY_SELLING_DECIMALS ] ?? null;
	}


	/** 販売価格の通貨シンボルを取得します。 */
	public function sellingSymbol(): ?string {
		return $this->attrs[ self::ATTRS_KEY_SELLING_SYMBOL ] ?? null;
	}
}

class BlockParser {
	/**
	 * 投稿内容をブロックに分割します。
	 *
	 * @param int $post_ID
	 * @return WP_Block_Parser_Block[]
	 */
	public function parse( string $content ): array {
		return array_map(
			function ( $block ) {
				[
					'blockName'    => $name,
					'attrs'        => $attrs,
					'innerBlocks'  => $inner_blocks,
					'innerHTML'    => $inner_html,
					'innerContent' => $inner_content
				] = $block;

				return new WP_Block_Parser_Block( $name, $attrs, $inner_blocks, $inner_html, $inner_content );
			},
			parse_blocks( $content )
		);
	}
}

class WidgetParser {
	/**
	 * ウィジェットブロックに関する情報を取得します。
	 *
	 * @param int $post_ID
	 * @return WP_Block_Parser_Block|null
	 */
	private function block( int $post_ID ): ?WP_Block_Parser_Block {
		$post_content = ( new PostContent( $post_ID ) )->getRaw();
		$blocks       = ( new BlockParser() )->parse( $post_content );
		$block_name   = BlockName::get(); // ウィジェットに付与されているブロック名

		// `blockName`プロパティが$block_nameと一致するブロックを取得
		$blocks = array_filter(
			$blocks,
			function ( $block ) use ( $block_name ) {
				return $block_name === $block->blockName;
			}
		);
		// インデックスを振り直す
		$blocks = array_values( $blocks );

		// ウィジェットは1投稿につき1つまでしか存在しない
		assert( count( $blocks ) <= 1, '[FD104DDE] Widget block must be only one in a post' );

		// ウィジェットが存在しない場合はnullを返す
		return 0 === count( $blocks ) ? null : $blocks[0];
	}

	/**
	 * ウィジェットブロックの属性を取得します。
	 *
	 * @param int $post_ID
	 * @return null|array
	 */
	public function attrs( int $post_ID ): ?array {
		$block = $this->block( $post_ID );
		if ( is_null( $block ) || is_null( $block->attrs ) ) {
			return null;
		}

		return $block->attrs;
	}
}
