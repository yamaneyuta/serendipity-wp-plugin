<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Post;
use Cornix\Serendipity\Core\Infrastructure\Database\Entity\PostImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\PaidContentTable;

class PostRepository {

	public function __construct( ?PaidContentTable $paid_content_table = null ) {
		$this->paid_content_table = $paid_content_table ?? new PaidContentTable( $GLOBALS['wpdb'] );
	}

	private PaidContentTable $paid_content_table;

	/**
	 * 指定した投稿IDの情報を取得します
	 */
	public function get( int $post_id ): Post {
		if ( false === get_post_status( $post_id ) ) {
			// 投稿が存在しない場合は例外を投げる
			throw new \InvalidArgumentException( "[7D8F3E0D] Post with ID {$post_id} does not exist." );
		}

		// テーブルから有料記事情報を取得
		$record = $this->paid_content_table->select( $post_id );

		return $record ? PostImpl::fromTableRecord( $record ) : new Post( $post_id, null, null, null );
	}

	public function save( Post $post ): void {

		if ( null === $post->paidContent() ) {
			// 有料記事の内容がnullの場合は、テーブルから削除
			$this->paid_content_table->delete( $post->id() );
		} else {
			// 有料記事の内容がある場合は、テーブルに保存
			$this->paid_content_table->set(
				$post->id(),
				$post->paidContent(),
				$post->sellingNetworkCategory(),
				$post->sellingPrice()
			);
		}
	}
}
