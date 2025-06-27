<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use InvalidArgumentException;
use phpseclib\Math\BigInteger;

/**
 * 数量等を表すクラス
 */
final class Amount {

	private function __construct( string $amount_text ) {
		if ( false !== strpos( $amount_text, '.' ) ) {
			// 小数点以下の末尾が0の場合は削除
			$amount_text = rtrim( $amount_text, '0' );
			// 小数点以下がなくなった場合は小数点も削除
			$amount_text = rtrim( $amount_text, '.' );
		}

		self::checkAmountText( $amount_text );
		$this->amount_text = $amount_text;
	}

	/** 値を10進数の文字列で保持 */
	private string $amount_text;

	public static function from( ?string $amount_text ): ?self {
		return null !== $amount_text ? new self( $amount_text ) : null;
	}

	public function equals( self $other ): bool {
		return $this->amount_text === $other->amount_text;
	}

	public function value(): string {
		return $this->amount_text;
	}

	public function __toString() {
		return $this->value();
	}

	public function mul( self $other ): self {
		$this_decimals  = strpos( $this->amount_text, '.' ) !== false
			? strlen( substr( strrchr( $this->amount_text, '.' ), 1 ) )
			: 0;
		$other_decimals = strpos( $other->amount_text, '.' ) !== false
			? strlen( substr( strrchr( $other->amount_text, '.' ), 1 ) )
			: 0;
		$this_int       = new BigInteger( str_replace( '.', '', $this->amount_text ), 10 );
		$other_int      = new BigInteger( str_replace( '.', '', $other->amount_text ), 10 );

		$result_int_text = $this_int->multiply( $other_int )->toString();
		$result_decimals = $this_decimals + $other_decimals;
		if ( 0 === $result_decimals ) {
			// 小数点以下がない場合はそのまま返す
			return new self( $result_int_text );
		} else {
			$is_negative     = strpos( $result_int_text, '-' ) === 0;
			$result_int_text = ltrim( $result_int_text, '-' );
			if ( strlen( $result_int_text ) <= $result_decimals ) {
				$result_int_text = str_repeat( '0', $result_decimals - strlen( $result_int_text ) + 1 ) . $result_int_text;
			}
			$integer_part    = substr( $result_int_text, 0, -$result_decimals );
			$fractional_part = substr( $result_int_text, -$result_decimals );
			$result_text_tmp = $integer_part . '.' . $fractional_part;

			return new self( $is_negative ? '-' . $result_text_tmp : $result_text_tmp );
		}
	}

	/**
	 *
	 * @param Amount   $other
	 * @param null|int $accuracy_decimals 最大精度。割り切れない場合は、指定した精度までの値を返す。
	 */
	public function div( self $other, int $accuracy_decimals ): self {
		$this_decimals  = strpos( $this->amount_text, '.' ) !== false
			? strlen( substr( strrchr( $this->amount_text, '.' ), 1 ) )
			: 0;
		$other_decimals = strpos( $other->amount_text, '.' ) !== false
			? strlen( substr( strrchr( $other->amount_text, '.' ), 1 ) )
			: 0;
		$this_int       = new BigInteger( str_replace( '.', '', $this->amount_text ), 10 );
		$other_int      = new BigInteger( str_replace( '.', '', $other->amount_text ), 10 );

		if ( '0' === $other_int->toString() ) {
			throw new \InvalidArgumentException( '[2D246909] Division by zero is not allowed.' );
		}

		// 一旦、有効桁数まで求められるように、分子の桁数を調整
		$this_int = $this_int->multiply( new BigInteger( '1' . str_repeat( '0', $accuracy_decimals ), 10 ) );
		// 割り算を行う
		/** @var BigInteger */
		$divided_quotient = $this_int->divide( $other_int )[0]; // 商を取得
		$total_decimals   = $this_decimals - $other_decimals + $accuracy_decimals;
		if ( 0 === $total_decimals ) {
			// 小数点以下がない場合はそのまま返す
			return new self( $divided_quotient->toString() );
		}

		$divided_quotient_text = $divided_quotient->toString();
		if ( strlen( $divided_quotient_text ) <= $total_decimals ) {
			// 小数点以下の桁数が足りない場合は0を追加
			$divided_quotient_text = str_repeat( '0', $total_decimals - strlen( $divided_quotient_text ) + 1 ) . $divided_quotient_text;
		}
		$integer_part    = substr( $divided_quotient_text, 0, -$total_decimals );
		$integer_part    = $integer_part === '' ? '0' : $integer_part; // 整数部分が空の場合は0にする
		$fractional_part = substr( $divided_quotient_text, -$total_decimals );
		// 最大精度以上の桁数がある場合は切り捨て
		if ( strlen( $fractional_part ) > $accuracy_decimals ) {
			$fractional_part = substr( $fractional_part, 0, $accuracy_decimals );
		}
		$result_text_tmp = $integer_part . '.' . $fractional_part;

		return new self( $result_text_tmp );
	}

	private static function checkAmountText( string $amount_text ): void {
		// 数値の形式をチェック
		if ( ! preg_match( '/^\-?\d+(\.\d*[1-9])?$/', $amount_text ) ) {
			throw new \InvalidArgumentException( '[275B6F0E] Invalid amount text: ' . $amount_text );
		}
	}
}
