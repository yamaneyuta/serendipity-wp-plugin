<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Post;

use Cornix\Serendipity\Core\Lib\Convert\HtmlFormat;

/**
 * 投稿IDを指定して投稿の本文を取得するクラス
 */
class PostContent {
	public function __construct( int $post_ID ) {
		$this->post_ID = $post_ID;
	}

	/** 投稿ID */
	private int $post_ID;

	/**
	 * 投稿の本文を取得します。
	 */
	public function getRaw(): string {
		$post = get_post( $this->post_ID );
		return $post->post_content;
	}

	/**
	 * 投稿の本文からHTMLコメントを削除したものを取得します。
	 */
	public function getCommentRemoved(): string {
		// HTMLコメントを削除
		return HtmlFormat::removeHtmlComments( $this->getRaw() );
	}
}
