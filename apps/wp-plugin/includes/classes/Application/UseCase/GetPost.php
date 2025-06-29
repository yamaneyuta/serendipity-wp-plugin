<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\PostDto;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class GetPost {
	public function __construct( PostRepository $post_repository, PostTitleProvider $post_title_provider ) {
		$this->post_repository     = $post_repository;
		$this->post_title_provider = $post_title_provider;
	}

	private PostRepository $post_repository;
	private PostTitleProvider $post_title_provider;

	public function handle( int $post_id ): ?PostDto {
		$post = $this->post_repository->get( new PostId( $post_id ) );
		return null !== $post ? PostDto::fromEntity( $post, $this->post_title_provider->getPostTitle( new PostId( $post_id ) ) ) : null;
	}
}
