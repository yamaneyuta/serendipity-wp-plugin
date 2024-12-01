<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Repository\Definition\TokenDefinition;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class Token {

	/** @var Token[] */
	private static array $cache = array();

	private function __construct( int $chain_ID, string $address ) {
		$this->chain_ID = $chain_ID;
		$this->address  = $address;
	}

	private int $chain_ID;
	private string $address;

	public function chainID(): int {
		return $this->chain_ID;
	}

	public function address(): string {
		return $this->address;
	}

	public function symbol(): string {
		return ( new TokenDefinition() )->symbol( $this->chain_ID, $this->address );
	}


	public static function from( int $chain_ID, string $address ): Token {
		if ( is_null( self::$cache[ $chain_ID ][ $address ] ?? null ) ) {
			// トークンのアドレスとして有効かどうかをチェック
			Judge::checkTokenAddress( $chain_ID, $address );

			self::$cache[ $chain_ID ][ $address ] = new Token( $chain_ID, $address );
		}

		return self::$cache[ $chain_ID ][ $address ];
	}
}
