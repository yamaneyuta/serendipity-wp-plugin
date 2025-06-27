<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Specification;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class TokensFilter {

	private array $filters = array();

	public function byChainID( ChainID $chain_id ): self {
		$this->filters[] = fn ( Token $token ) => $token->chainID()->value() === $chain_id->value();
		return $this;
	}

	public function byAddress( Address $address ): self {
		$this->filters[] = fn ( Token $token ) => $token->address()->equals( $address );
		return $this;
	}

	public function bySymbol( Symbol $symbol ): self {
		$this->filters[] = fn ( Token $token ) => $token->symbol()->equals( $symbol );
		return $this;
	}

	public function byIsPayable( bool $is_payable ): self {
		$this->filters[] = fn ( Token $token ) => $token->isPayable() === $is_payable;
		return $this;
	}

	/**
	 * フィルタを適用した結果を返します。
	 *
	 * @param Token[] $tokens
	 * @return Token[]
	 */
	public function apply( array $tokens ): array {
		foreach ( $this->filters as $filter ) {
			$tokens = array_filter( $tokens, $filter );
		}
		return $tokens;
	}
}
