<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Content;

class Content {
	// コンストラクタ
	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
	}

	/** @var int */
	private $post_id;

	/**
	 * サムネイル画像のURLを取得します。
	 *
	 * @return null|string
	 */
	public function getThumbnailUrl(): ?string {
		// サムネイル画像のURLを取得(存在しない場合はnull)
		$post_thumbnail_url = get_the_post_thumbnail_url( $this->post_id );
		$post_thumbnail_url = $post_thumbnail_url === false ? null : $post_thumbnail_url;

		return $post_thumbnail_url;
	}
}
