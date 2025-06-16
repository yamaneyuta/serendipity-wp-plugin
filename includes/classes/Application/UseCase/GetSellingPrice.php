<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Infrastructure\Factory\PostRepositoryFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use wpdb;

class GetSellingPrice {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function handle( int $post_id ): ?Price {
		$postRepository = ( new PostRepositoryFactory( $this->wpdb ) )->create();
		return $postRepository->get( $post_id )->sellingPrice();
	}
}
