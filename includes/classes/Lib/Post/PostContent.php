<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Post;

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
	public function get(): string {
		$post = get_post( $this->post_ID );
		return $post->post_content;
	}
}
