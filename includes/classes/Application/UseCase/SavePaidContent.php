<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Infrastructure\Factory\PostRepositoryFactory;
use Cornix\Serendipity\Core\Domain\Entity\PaidContent;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use wpdb;

class SavePaidContent {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function handle( int $post_id, PaidContent $paid_content, ?NetworkCategoryID $selling_network_category_id, ?Price $selling_price ): void {
		$post_repository = ( new PostRepositoryFactory( $this->wpdb ) )->create();

		$post = $post_repository->get( $post_id );

		// 有料記事の内容を更新
		$post->setPaidContent( $paid_content );
		$post->setSellingNetworkCategoryID( $selling_network_category_id );
		$post->setSellingPrice( $selling_price );

		$post_repository->save( $post );
	}
}
