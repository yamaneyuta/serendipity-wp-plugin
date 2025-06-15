<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Specification;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class OraclesFilter {

	private array $filters = array();

	public function byChainID( ChainID $chain_id ): self {
		$this->filters[] = fn ( Oracle $oracle ) => $oracle->chain()->id()->equals( $chain_id );
		return $this;
	}
	public function byAddress( Address $address ): self {
		$this->filters[] = fn ( Oracle $oracle ) => $oracle->address()->equals( $address );
		return $this;
	}
	public function bySymbolPair( SymbolPair $symbol_pair ): self {
		$this->filters[] = fn ( Oracle $oracle ) => $oracle->baseSymbol() === $symbol_pair->base()
			&& $oracle->quoteSymbol() === $symbol_pair->quote();
		return $this;
	}
	public function byConnectable(): self {
		$this->filters[] = fn ( Oracle $oracle ) => $oracle->chain()->connectable();
		return $this;
	}

	/**
	 * フィルタを適用した結果を返します。
	 *
	 * @param Oracle[] $oracles
	 * @return Oracle[]
	 */
	public function apply( array $oracles ): array {
		foreach ( $this->filters as $filter ) {
			$oracles = array_filter( $oracles, $filter );
		}
		return $oracles;
	}
}
