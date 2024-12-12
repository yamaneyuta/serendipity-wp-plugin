<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Repository\Definition\NativeTokenDefinition;
use Cornix\Serendipity\Core\Lib\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;

class TokenType {

	/** @var TokenType[] */
	private static array $cache = array();

	private function __construct( int $chain_ID, string $address, string $symbol, int $decimals ) {
		$this->chain_ID = $chain_ID;
		$this->address  = $address;
		$this->symbol   = $symbol;
		$this->decimals = $decimals;
	}

	private int $chain_ID;
	private string $address;
	private string $symbol;
	private int $decimals;

	public function chainID(): int {
		return $this->chain_ID;
	}

	public function address(): string {
		return $this->address;
	}

	public function symbol(): string {
		return $this->symbol;
	}

	public function decimals(): int {
		return $this->decimals;
	}


	public static function from( int $chain_ID, string $address, ?string $symbol = null, ?int $decimals = null ): TokenType {
		if ( is_null( self::$cache[ $chain_ID ][ $address ] ?? null ) ) {
			// トークンのアドレスとして有効かどうかをチェック
			Judge::checkTokenAddress( $chain_ID, $address );

			if ( Ethers::zeroAddress() === $address ) {
				$symbol   = ( new NativeTokenDefinition() )->getSymbol( $chain_ID );
				$decimals = ( new NativeTokenDefinition() )->getDecimals( $chain_ID );
			}

			// TODO: TokenTypeクラスとTokenDataの相互呼び出しが複雑になっているのでリファクタしたい
			if ( is_null( $symbol ) || is_null( $decimals ) ) {
				$tokens = ( new TokenData() )->get( $chain_ID, $address );
				if ( count( $tokens ) !== 1 ) {
					throw new \InvalidArgumentException( '[CB075A2E] Invalid token. chain id: ' . $chain_ID . ', address: ' . $address . ', count: ' . count( $tokens ) );
				}
				return $tokens[0];
			}

			self::$cache[ $chain_ID ][ $address ] = new TokenType( $chain_ID, $address, $symbol, $decimals );
		}

		return self::$cache[ $chain_ID ][ $address ];
	}
}
