<?php

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * チェーンIDを表す値オブジェクト
 */
class ChainID {

	public function __construct( int $chain_id_value ) {
		if ( $chain_id_value <= 0 ) {
			throw new \InvalidArgumentException( '[44CF8BCC] Chain ID must be a positive integer.' );
		}
		$this->chain_id_value = $chain_id_value;
	}
	private int $chain_id_value;

	public function value(): int {
		return $this->chain_id_value;
	}

	public function equals( ChainID $other ): bool {
		return $this->chain_id_value === $other->value();
	}

	public function __toString(): string {
		return (string) $this->chain_id_value;
	}

	public static function fromNullableValue( ?int $chain_id_value ): ?ChainID {
		return $chain_id_value === null ? null : new ChainID( $chain_id_value );
	}

	/** イーサリアムメインネット(L1) */
	private const ETH_MAINNET = 1;
	public static function ethMainnet(): ChainID {
		return new ChainID( self::ETH_MAINNET );
	}

	/** PrivatenetL1に位置付けられたチェーンID */
	private const PRIVATENET_L1 = 31337; // 0x7a69
	public static function privatenet1(): ChainID {
		return new ChainID( self::PRIVATENET_L1 );
	}
	/** PrivatenetL2に位置付けられたチェーンID(L2) ※実際はロールアップを行っていない、単に独立したネットワーク */
	private const PRIVATENET_L2 = 1337;  // 0x539
	public static function privatenet2(): ChainID {
		return new ChainID( self::PRIVATENET_L2 );
	}
}
