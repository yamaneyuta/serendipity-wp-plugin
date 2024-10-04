<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes;

use Cornix\Serendipity\Core\Lib\Post\PostContent;
use Cornix\Serendipity\Core\Lib\Repository\BlockName;
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
		if ( ! isset( $widget_parser_block['attrs'] ) ) {
			return null;
		}

		return WidgetAttributesType::fromAttrs( $widget_parser_block['attrs'] );
	}

	/**
	 * ウィジェットのブロック情報を取得します。
	 */
	private function getWidgetParserBlock(): ?array {
		$post_content = $this->post_content->getRaw();
		$blocks       = parse_blocks( $post_content );
		$block_name   = BlockName::get(); // ウィジェットに付与されているブロック名

		// `blockName`プロパティが$block_nameと一致するブロックを取得
		$blocks = array_filter(
			$blocks,
			function ( $block ) use ( $block_name ) {
				return $block_name === $block['blockName'];
			}
		);
		// インデックスを振り直す
		$blocks = array_values( $blocks );

		// ウィジェットは1投稿につき1つまでしか存在しない
		assert( count( $blocks ) <= 1, '[FD104DDE] Widget block must be only one in a post' );

		// ウィジェットが存在しない場合はnullを返す
		return 0 === count( $blocks ) ? null : $blocks[0];
	}
}
