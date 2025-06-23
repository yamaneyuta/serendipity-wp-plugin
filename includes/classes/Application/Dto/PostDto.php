<?php

namespace Cornix\Serendipity\Core\Application\Dto;

use Cornix\Serendipity\Core\Domain\Entity\Post;

class PostDto {

	private function __construct( int $id, string $title ) {
		$this->id    = $id;
		$this->title = $title;
	}

	private int $id;
	private string $title;

	public function id(): int {
		return $this->id;
	}
	public function title(): string {
		return $this->title;
	}

	public static function fromEntity( Post $post, string $title ): self {
		return new self(
			$post->id(),
			$title
		);
	}
}
