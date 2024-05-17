<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Posts;

use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\Strings;

class ContentTrimer {
	private function __construct() {
	}

	public static function trimForView( string $content ): string {
		return ( new ContentTrimer() )->trim( $content, true, true, false );
	}
	public static function trimOnlyFree( string $content ): string {
		return ( new ContentTrimer() )->trim( $content, true, false, false );
	}
	public static function trimOnlyPaid( string $content ): string {
		return ( new ContentTrimer() )->trim( $content, false, false, true );
	}

	private function trim( string $content, bool $includeFree, bool $includeWidget, bool $includePaid ): string {

		// 投稿にウィジェットを配置するエリアが含まれない場合は投稿内容をそのまま返す
		if ( false === $this->isWidgetTagIncluded( $content ) ) {
			return $content;
		}

		// ウィジェットが配置されているエリアの先頭の位置を取得
		$widget_start_pos = $this->getWidgetStartIndex( $content );
		// 有料エリアの先頭の位置を取得
		$paid_content_start_pos = $this->getPaidContentStartIndex( $content );

		// どちらかの位置が取得できていない、ということはあり得ない
		if ( false === $widget_start_pos || false === $paid_content_start_pos ) {
			throw new \Exception( '{F3216A36-F5CD-417B-B4B5-8038AB91AB5B}' );
		}

		$free_content   = Strings::substr( $content, 0, $widget_start_pos );
		$widget_content = Strings::substr( $content, $widget_start_pos, $paid_content_start_pos - $widget_start_pos );
		$paid_content   = Strings::substr( $content, $paid_content_start_pos );

		return ( $includeFree ? $free_content : '' ) . ( $includeWidget ? $widget_content : '' ) . ( $includePaid ? $paid_content : '' );
	}

	private function isWidgetTagIncluded( string $content ): bool {
		return false !== Strings::strpos( $content, $this->getWidgetClassName() );
	}

	private function getWidgetClassName(): string {
		$className = Constants::get( 'className.widget' );
		if ( 0 === Strings::strlen( $className ) ) {
			throw new \Exception( '{F7C3727F-0E59-4896-8551-6C4FE0DF7640}' );
		}
		return $className;
	}

	private function getWidgetTag( string $content ): string {
		// `the_content`フィルタを通していないかどうかを判定
		$is_not_filtered = false !== Strings::strpos( $content, '<!-- wp:' );

		$start_str = $is_not_filtered ? '<!-- wp:' : '<div';
		$end_str   = $is_not_filtered ? '-->' : '</div>';

		$start_indexes    = Strings::all_strpos( $content, $start_str );
		$class_name_index = Strings::strpos( $content, $this->getWidgetClassName() );
		$end_indexes      = Strings::all_strpos( $content, $end_str );

		// ウィジェットの開始タグの位置を取得
		$start_index = $start_indexes[0];
		foreach ( $start_indexes as $index ) {
			if ( $index > $class_name_index ) {
				break;
			}
			$start_index = $index;
		}
		// ウィジェットの終了タグの位置を取得
		$end_index = $end_indexes[ count( $end_indexes ) - 1 ];
		foreach ( $end_indexes as $index ) {
			if ( $index < $class_name_index ) {
				continue;
			}
			$end_index = $index;
			break;
		}
		// 終了タグの場合は、終了タグの文字数だけ追加
		$end_index += Strings::strlen( $end_str );

		// ウィジェットのタグ部分にあたる文字列を返す
		return Strings::substr( $content, $start_index, $end_index - $start_index );
	}

	private function getWidgetStartIndex( string $content ): int {
		$widget_tag = $this->getWidgetTag( $content );
		return Strings::strpos( $content, $widget_tag );
	}

	private function getPaidContentStartIndex( string $content ): int {
		$widget_tag = $this->getWidgetTag( $content );
		return $this->getWidgetStartIndex( $content ) + Strings::strlen( $widget_tag );
	}
}
