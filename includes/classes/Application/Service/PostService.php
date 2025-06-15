<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Entity\PaidContent;
use Cornix\Serendipity\Core\Domain\Entity\Post;
use Cornix\Serendipity\Core\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;

class PostService {

	public function __construct( ?PostRepository $post_repository = null ) {
		$this->post_repository = $post_repository ?? new PostRepository();
	}
	private PostRepository $post_repository;

	public function get( int $post_id ): Post {
		return $this->post_repository->get( $post_id );
	}

	public function savePaidContent( int $post_id, PaidContent $paid_content, ?NetworkCategory $selling_network_category, ?Price $selling_price ): void {
		$post = $this->post_repository->get( $post_id );

		// 有料記事の内容を更新
		$post->setPaidContent( $paid_content );
		$post->setSellingNetworkCategory( $selling_network_category );
		$post->setSellingPrice( $selling_price );

		$this->post_repository->save( $post );
	}

	public function deletePaidContent( int $post_id ): void {
		$post = $this->post_repository->get( $post_id );

		// 有料記事の内容を削除
		$post->setPaidContent( null );
		$post->setSellingNetworkCategory( null );
		$post->setSellingPrice( null );

		$this->post_repository->save( $post );
	}
}
