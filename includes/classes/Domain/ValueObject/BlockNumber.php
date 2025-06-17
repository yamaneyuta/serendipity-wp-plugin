<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use phpseclib\Math\BigInteger;

/**
 * ブロック番号を表すクラス
 */
class BlockNumber {

	private function __construct( BigInteger $block_number ) {
		$this->block_number = $block_number;
	}

	private BigInteger $block_number;

	/**
	 * @param null|int|string|BigInteger $block_number
	 */
	public static function from( $block_number ): ?BlockNumber {
		if ( null === $block_number ) {
			return null;
		} elseif ( is_int( $block_number ) ) {
			return new BlockNumber( new BigInteger( $block_number, 10 ) );
		} elseif ( is_string( $block_number ) && Validate::isHex( $block_number ) ) {
			return new BlockNumber( new BigInteger( $block_number, 16 ) );
		} elseif ( $block_number instanceof BigInteger ) {
			return new BlockNumber( $block_number );
		} else {
			throw new \InvalidArgumentException( '[DEE2905B] Invalid block number. - block_number: ' . var_export( $block_number, true ) );
		}
	}

	/**
	 * 現在のブロック番号に引数の値を加算した新しいインスタンスを取得します。
	 */
	public function add( int $addend ): BlockNumber {
		return new BlockNumber( $this->block_number->add( new BigInteger( $addend, 10 ) ) );
	}

	/**
	 * 現在のブロック番号から引数の値を減算した新しいインスタンスを取得します。
	 */
	public function sub( int $subtrahend ): BlockNumber {
		return new BlockNumber( $this->block_number->subtract( new BigInteger( $subtrahend, 10 ) ) );
	}

	/**
	 * ブロック番号を比較します。
	 *
	 * $x > $y: $x->compare($y) > 0
	 * $x < $y: $x->compare($y) < 0
	 * $x == $y: $x->compare($y) == 0
	 */
	public function compare( BlockNumber $other ): int {
		return $this->block_number->compare( $other->block_number );
	}

	/**
	 * ブロック番号を16進数表記で取得します。
	 */
	public function hex(): string {
		return HexFormat::toHex( $this->block_number );
	}

	/**
	 * ブロック番号を整数で取得します。
	 */
	public function int(): int {
		return HexFormat::toInt( $this->hex() );
	}

	public function __toString(): string {
		return (string) $this->int();
	}
}
