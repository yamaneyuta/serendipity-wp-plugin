<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Post;

use Cornix\Serendipity\Core\Lib\Repository\ClassName;
use Cornix\Serendipity\Core\Lib\Strings\Strings;

/**
 * 投稿内容をフィルタリングします。
 */
class ContentFilter {

	public function __construct( string $content ) {
		// $contentは、コメントを含まないHTMLタグ
		assert( ! Strings::strpos( $content, '<!-- wp:' ), "[9BA30031] Invalid content. - content: $content" );

		$this->content   = $content;
		$this->className = ( new ClassName() )->getBlock();
	}

	/** 投稿内容 */
	private string $content;

	/** ウィジェットに付与されるクラス名 */
	private string $className;

	/**
	 * 投稿の無料部分を取得します。
	 *
	 * @return string|null 投稿の無料部分
	 */
	public function getFree(): ?string {
		$widget_start_pos = $this->getWidgetStartPos();
		if ( null === $widget_start_pos ) {
			// ウィジェットが配置されていない場合はnullを返す
			return null;
		}

		// 無料部分は0～ウィジェットの開始位置まで
		return Strings::substr( $this->content, 0, $widget_start_pos );
	}

	public function getWidget(): ?string {
		$widget_start_pos = $this->getWidgetStartPos();
		if ( null === $widget_start_pos ) {
			// ウィジェットが配置されていない場合はnullを返す
			return null;
		}

		// ウィジェットの開始位置から終了位置まで
		$widget_end_pos = $this->getWidgetEndPos();
		assert( is_int( $widget_end_pos ), "[B4EC65DA] Invalid content. - content: $this->content" );
		return Strings::substr( $this->content, $widget_start_pos, $widget_end_pos - $widget_start_pos );
	}

	/**
	 * 投稿の有料部分を取得します。
	 *
	 * @return null|string 投稿の有料部分
	 */
	public function getPaid(): ?string {
		$widget_end_pos = $this->getWidgetEndPos();
		if ( null === $widget_end_pos ) {
			// ウィジェットが配置されていない場合はnullを返す
			return null;
		}

		// ウィジェットの終了位置から最後まで
		return Strings::substr( $this->content, $widget_end_pos );
	}

	/**
	 * ウィジェットのクラス名の出現する位置を取得します。
	 *
	 * @return int|null $this->contentにおけるウィジェットのクラス名の出現位置。クラス名(ウィジェット)が存在しない場合はnull。
	 */
	private function getWidgetClassNamePos(): ?int {
		$pos = Strings::strpos( $this->content, $this->className );
		return false === $pos ? null : $pos;
	}

	/**
	 * ウィジェットの開始位置を取得します。
	 *
	 * @return null|int $this->contentにおけるウィジェットの開始位置。ウィジェットが存在しない場合はnull。
	 */
	private function getWidgetStartPos(): ?int {
		$class_name_pos = $this->getWidgetClassNamePos();
		if ( null === $class_name_pos ) {
			// ウィジェットが配置されていない場合はnullを返す
			return null;
		}

		// `<aside `の位置をすべて取得(ウィジェットが配置されている場合はasideタグが1つ以上存在する)
		$elm_positions = Strings::all_strpos( $this->content, '<aside ' );
		assert( count( $elm_positions ) > 0, "[26EAB256] Invalid content. - content: $this->content" );

		// ウィジェットのタグ開始位置を取得
		$widget_start_pos = max( array_filter( $elm_positions, fn( $pos ) => $pos < $class_name_pos ) );
		assert( is_int( $widget_start_pos ), "[805CD4D1] Invalid content. - content: $this->content" );

		// ウィジェットのタグ開始位置を返す
		return $widget_start_pos;
	}

	private function getWidgetEndPos(): ?int {
		$class_name_pos = $this->getWidgetClassNamePos();
		if ( null === $class_name_pos ) {
			// ウィジェットが配置されていない場合はnullを返す
			return null;
		}

		// ウィジェットのクラス名の位置から開始して最初に出現する閉じタグの位置を取得
		$end_tag        = '</aside>';
		$widget_end_pos = Strings::strpos( $this->content, $end_tag, $class_name_pos );
		assert( false !== $widget_end_pos, "[CCA43C75] Invalid content. - content: $this->content" );
		return $widget_end_pos + Strings::strlen( $end_tag );
	}
}
