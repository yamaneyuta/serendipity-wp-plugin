<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use IteratorAggregate;
use Traversable;

/**
 * @template T
 */
abstract class ObjectValueArrayBase implements IteratorAggregate {

	/**
	 * @param T[] $items
	 */
	protected function __construct( array $items ) {
		$this->items = $items;
	}

	/** @var T[] */
	protected array $items;

	public function getIterator(): Traversable {
		yield from $this->items;
	}

	/**
	 * @param callable(T):bool $callback
	 * @return T|null
	 */
	public function find( callable $callback ) {
		// array_findはPHP8.0以降で使用可能。後方互換性のためにループで実装
		foreach ( $this->items as $item ) {
			if ( $callback( $item ) ) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * @param callable(T):bool $callback
	 * @return static
	 */
	public function filter( callable $callback ): ObjectValueArrayBase {
		$filtered = array_filter( $this->items, $callback );
		return new static( $filtered );
	}

	/**
	 * @return T[]
	 */
	public function toArray(): array {
		return $this->items;
	}
}
