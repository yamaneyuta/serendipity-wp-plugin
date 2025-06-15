<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Factory\PostRepositoryFactory;
use wpdb;

class DeletePaidContent {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function handle( int $post_id ): void {
		$post_repository = ( new PostRepositoryFactory( $this->wpdb ) )->create();

		$post = $post_repository->get( $post_id );

		// 有料記事の内容を削除
		$post->setPaidContent( null );
		$post->setSellingNetworkCategory( null );
		$post->setSellingPrice( null );

		$post_repository->save( $post );
	}
}
