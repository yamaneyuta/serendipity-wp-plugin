<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * 投稿IDを表す値オブジェクト
 */
class PostId {

	public function __construct( int $post_id ) {
		if ( $post_id <= 0 ) {
			throw new \InvalidArgumentException( '[8B2A1F3C] Post ID must be a positive integer.' );
		}
		$this->post_id = $post_id;
	}

	private int $post_id;

	public function value(): int {
		return $this->post_id;
	}

	public function equals( PostId $other ): bool {
		return $this->post_id === $other->value();
	}

	public function __toString(): string {
		return (string) $this->post_id;
	}

	public static function fromNullableValue( ?int $post_id ): ?PostId {
		return $post_id === null ? null : new PostId( $post_id );
	}
}
