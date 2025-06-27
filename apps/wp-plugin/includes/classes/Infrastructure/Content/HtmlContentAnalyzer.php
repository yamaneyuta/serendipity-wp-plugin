<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\Content;

use Cornix\Serendipity\Core\Infrastructure\Format\HtmlFormat;
use Cornix\Serendipity\Core\Lib\Strings\Strings;

class HtmlContentAnalyzer {

	public function __construct( string $content ) {
		// HTMLタグは分析対象から除外
		$this->content = HtmlFormat::removeHtmlComments( $content );
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
