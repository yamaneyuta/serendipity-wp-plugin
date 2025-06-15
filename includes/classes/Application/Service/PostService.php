<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Entity\Post;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\PostRepository;

class PostService {

	public function __construct( ?PostRepository $post_repository = null ) {
		$this->post_repository = $post_repository ?? new PostRepository();
	}
	private PostRepository $post_repository;

	public function get( int $post_id ): Post {
		return $this->post_repository->get( $post_id );
	}
}
