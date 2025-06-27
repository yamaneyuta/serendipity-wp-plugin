<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Repository\Name\BlockName;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use WP_Block_Parser_Block;

class WidgetAttributes {
	private const ATTRS_KEY_SELLING_NETWORK_CATEGORY_ID = 'sellingNetworkCategoryID';
	private const ATTRS_KEY_SELLING_AMOUNT              = 'sellingAmount';
	private const ATTRS_KEY_SELLING_SYMBOL              = 'sellingSymbol';

	private function __construct( array $attrs ) {
		$this->attrs = $attrs;
	}

	public static function from( ?NetworkCategoryID $network_category_id, ?Price $selling_price ): WidgetAttributes {
		return new self(
			array(
				self::ATTRS_KEY_SELLING_NETWORK_CATEGORY_ID => $network_category_id ? $network_category_id->value() : null,
				self::ATTRS_KEY_SELLING_AMOUNT => $selling_price ? $selling_price->amount()->value() : null,
				self::ATTRS_KEY_SELLING_SYMBOL => $selling_price ? $selling_price->symbol()->value() : null,
			)
		);
	}

	public static function fromContent( string $content ): ?WidgetAttributes {
		$attrs = ( new WidgetParser() )->attrsFromContent( $content );

		return is_null( $attrs ) ? null : new self( $attrs );
	}

	private array $attrs;

	public function toArray(): array {
		return $this->attrs;
	}


	/** 販売対象のネットワークカテゴリを取得します。 */
	public function sellingNetworkCategoryID(): ?NetworkCategoryID {
		return NetworkCategoryID::from( $this->attrs[ self::ATTRS_KEY_SELLING_NETWORK_CATEGORY_ID ] ?? null );
	}

	/** 販売価格を取得します。 */
	public function sellingPrice(): ?Price {
		$amount = Amount::from( $this->sellingAmount() );
		$symbol = Symbol::from( $this->sellingSymbol() );

		if ( is_null( $amount ) || is_null( $symbol ) ) {
			return null;
		}

		return new Price( $amount, $symbol );
	}

	/** 販売価格を取得します */
	private function sellingAmount(): ?string {
		return $this->attrs[ self::ATTRS_KEY_SELLING_AMOUNT ] ?? null;
	}


	/** 販売価格の通貨シンボルを取得します。 */
	private function sellingSymbol(): ?string {
		return $this->attrs[ self::ATTRS_KEY_SELLING_SYMBOL ] ?? null;
	}
}

class BlockParser {
	/**
	 * 投稿内容をブロックに分割します。
	 *
	 * @param string $content
	 * @return WP_Block_Parser_Block[]
	 */
	public function parse( string $content ): array {
		$blocks = parse_blocks( $content );
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
			$blocks
		);
	}
}

class WidgetParser {
	/**
	 * ウィジェットブロックに関する情報を取得します。
	 *
	 * @param string $post_content
	 * @return WP_Block_Parser_Block|null
	 */
	private function block( string $post_content ): ?WP_Block_Parser_Block {
		$blocks     = ( new BlockParser() )->parse( $post_content );
		$block_name = BlockName::get(); // ウィジェットに付与されているブロック名

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
	 */
	public function attrsFromContent( string $content ): ?array {
		$block = $this->block( $content );
		if ( is_null( $block ) || is_null( $block->attrs ) ) {
			return null;
		}

		return $block->attrs;
	}
}
