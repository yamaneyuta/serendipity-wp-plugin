<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Domain\Repository\PostRepository;

class DeletePaidContent {
	public function __construct( PostRepository $post_repository ) {
		$this->post_repository = $post_repository;
	}

	private PostRepository $post_repository;

	public function handle( int $post_id ): void {
		$post = $this->post_repository->get( $post_id );

		// 有料記事の内容を削除
		$post->deletePaidContent();

		$this->post_repository->save( $post );
	}
}
