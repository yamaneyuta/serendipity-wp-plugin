<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepositoryImpl;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class Price {
	public function __construct( Amount $amount, Symbol $symbol ) {
		Validate::checkSymbolObject( $symbol );

		$this->amount = $amount;
		$this->symbol = $symbol;
	}

	private Amount $amount;
	private Symbol $symbol;

	/**
	 * 金額の数量を取得します。
	 */
	public function amount(): Amount {
		return $this->amount;
	}

	/** 通貨記号(`USD`, `ETH`等)を取得します。記号(`$`等)ではない。 */
	public function symbol(): Symbol {
		return $this->symbol;
	}

	/**
	 * 指定したネットワークにおけるトークンの数量に変換します。
	 */
	public function toTokenAmount( ChainID $chain_ID ): Amount {
		// そのトークン1単位における小数点以下桁数。ETHであれば18。
		$tokens_filter = ( new TokensFilter() )->byChainID( $chain_ID )->bySymbol( $this->symbol );
		$tokens        = $tokens_filter->apply( ( new TokenRepositoryImpl() )->all() );

		if ( 1 !== count( $tokens ) ) {
			throw new \InvalidArgumentException( '[1644531E] Invalid token data. - chainID: ' . $chain_ID->value() . ', symbol: ' . $this->symbol->value() . ', count: ' . count( $tokens ) );
		}
		$token = array_values( $tokens )[0]; // 1つだけなので、配列の最初の要素を取得

		$token_decimals = $token->decimals();

		return $this->amount->mul( Amount::from( (string) ( 10 ** $token_decimals ) ) );
	}
}
