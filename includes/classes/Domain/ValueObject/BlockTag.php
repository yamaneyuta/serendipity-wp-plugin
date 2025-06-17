<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * `latest`など、evm_getBlockByNumber等で指定できるタグを表すクラス
 */
final class BlockTag {
	private const LATEST = 'latest';

	// 以下は必要になったタイミングでアンコメント
	// private const EARLIEST = 'earliest';
	// private const PENDING = 'pending';
	// private const SAFE = 'safe';
	// private const FINALIZED = 'finalized';

	private string $tag;

	private function __construct( string $tag ) {
		if ( ! in_array( $tag, array( self::LATEST /*, self::EARLIEST, self::PENDING, self::SAFE, self::FINALIZED */ ), true ) ) {
			throw new \InvalidArgumentException( '[62BF0CAE] Invalid block tag: ' . $tag );
		}

		$this->tag = $tag;
	}

	public function value(): string {
		return $this->tag;
	}

	public function equals( BlockTag $other ): bool {
		return $this->tag === $other->tag;
	}

	public function __toString(): string {
		return $this->tag;
	}

	public static function latest(): self {
		return new self( self::LATEST );
	}
}
