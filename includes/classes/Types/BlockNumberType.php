<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use phpseclib\Math\BigInteger;

/**
 * ブロック番号を表すクラス
 */
class BlockNumberType {

	private function __construct( BigInteger $block_number ) {
		$this->block_number = $block_number;
	}

	private BigInteger $block_number;

	public static function from( $block_number ): BlockNumberType {
		if ( is_int( $block_number ) ) {
			return new BlockNumberType( new BigInteger( $block_number, 10 ) );
		} elseif ( is_string( $block_number ) && Judge::isHex( $block_number ) ) {
			return new BlockNumberType( new BigInteger( $block_number, 16 ) );
		} elseif ( $block_number instanceof BigInteger ) {
			return new BlockNumberType( $block_number );
		} else {
			throw new \InvalidArgumentException( '[DEE2905B] Invalid block number. - block_number: ' . var_export( $block_number, true ) );
		}
	}

	/**
	 * 現在のブロック番号に引数の値を加算した新しいインスタンスを取得します。
	 */
	public function add( int $addend ): BlockNumberType {
		return new BlockNumberType( $this->block_number->add( new BigInteger( $addend, 10 ) ) );
	}

	/**
	 * 現在のブロック番号から引数の値を減算した新しいインスタンスを取得します。
	 */
	public function sub( int $subtrahend ): BlockNumberType {
		return new BlockNumberType( $this->block_number->subtract( new BigInteger( $subtrahend, 10 ) ) );
	}

	/**
	 * ブロック番号を比較します。
	 *
	 * $x > $y: $x->compare($y) > 0
	 * $x < $y: $x->compare($y) < 0
	 * $x == $y: $x->compare($y) == 0
	 */
	public function compare( BlockNumberType $other ): int {
		return $this->block_number->compare( $other->block_number );
	}

	/**
	 * ブロック番号を16進数表記で取得します。
	 */
	public function hex(): string {
		return Hex::from( $this->block_number );
	}

	/**
	 * ブロック番号を整数で取得します。
	 */
	public function int(): int {
		return Hex::toInt( $this->hex() );
	}
}
