<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Post;

use Cornix\Serendipity\Core\Lib\Strings\Strings;

class ContentAnalyzer {

	public function __construct( string $content ) {
		// $contentは、コメントを含まないHTMLタグ
		assert( ! Strings::strpos( $content, '<!--' ), "[5F89E24A] Invalid content. - content: $content" );

		$this->content = $content;
	}

	/** 投稿内容(HTMLコメントは含まない / HTMLタグを含む) */
	private string $content;

	/**
	 * 文字数を取得します。
	 */
	public function getCharacterCount(): int {
		// $this->contentからHTMLタグを除去
		$content = strip_tags( $this->content );

		// $contentから空白文字を除去
		$content = preg_replace( '/\s+/', '', $content );

		return Strings::strlen( $content );
	}

	/**
	 * 画像数を取得します。
	 */
	public function getImageCount(): int {
		// $this->contentからimgタグを取得
		preg_match_all( '/<img[^>]+>/i', $this->content, $matches );

		return count( $matches[0] );
	}
}
