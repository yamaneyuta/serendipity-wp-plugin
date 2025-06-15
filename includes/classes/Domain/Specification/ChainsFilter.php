<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Specification;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class ChainsFilter {

	private array $filters = array();

	public function byChainID( ChainID $chain_id ): self {
		$this->filters[] = fn ( Chain $chain ) => $chain->id()->equals( $chain_id );
		return $this;
	}

	public function byNetworkCategory( NetworkCategory $category ): self {
		$this->filters[] = fn ( Chain $chain ) => $chain->networkCategory()->id() === $category->id();
		return $this;
	}

	public function byConnectable(): self {
		$this->filters[] = fn ( Chain $chain ) => $chain->connectable();
		return $this;
	}

	/**
	 * フィルタを適用してチェーンの配列を返します。
	 *
	 * @param Chain[] $chains
	 * @return Chain[]
	 */
	public function apply( array $chains ): array {
		foreach ( $this->filters as $filter ) {
			$chains = array_filter( $chains, $filter );
		}
		return array_values( $chains );
	}
}
