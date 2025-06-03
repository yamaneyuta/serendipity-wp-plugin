<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Entity\Post;
use Cornix\Serendipity\Core\Repository\TableGateway\PaidContentTable;

class PostRepository {

	public function __construct( ?PaidContentTable $oracle_table = null ) {
		$this->paid_content_table = $oracle_table ?? new PaidContentTable( $GLOBALS['wpdb'] );
	}

	private PaidContentTable $paid_content_table;

	/**
	 * 指定した投稿IDの情報を取得します
	 */
	public function get( int $post_id ): ?Post {
		$record = $this->paid_content_table->select( $post_id );
		return null === $record ? null : Post::fromTableRecord( $record );
	}
}
