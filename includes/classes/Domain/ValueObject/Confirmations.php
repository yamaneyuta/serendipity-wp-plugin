<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * ブロックチェーンのトランザクションの確認数を表すクラス
 */
final class Confirmations {

	private function __construct( $confirmations_value ) {
		self::checkConfirmationsValue( $confirmations_value );
		$this->confirmations_value = $confirmations_value;
	}

	/** @var int|string */
	private $confirmations_value;

	/**
	 * @param int|string|null $confirmations_value
	 * @return Confirmations|null
	 */
	public static function from( $confirmations_value ): ?self {
		if ( null === $confirmations_value ) {
			return null;
		} elseif ( is_string( $confirmations_value ) && 1 === preg_match( '/^-?\d+$/', $confirmations_value ) ) {
			// '1'のような数値の文字列が来た場合はint型に変換してインスタンスを生成
			return new self( (int) $confirmations_value );
		} else {
			return new self( $confirmations_value );
		}
	}

	/**
	 * @return int|string
	 */
	public function value() {
		return $this->confirmations_value;
	}

	public function __toString(): string {
		return (string) $this->confirmations_value;
	}

	public function equals( self $other ): bool {
		return $this->confirmations_value === $other->confirmations_value;
	}

	/**
	 * 確認数の値が正しい形式であることを確認する
	 *
	 * @param int|string $confirmations_value
	 */
	private static function checkConfirmationsValue( $confirmations_value ): void {
		if ( is_int( $confirmations_value ) ) {
			// confirmationsが数値の場合、1以上の整数であることを確認
			if ( $confirmations_value <= 0 ) {
				throw new \InvalidArgumentException( '[5DCC888A] Invalid confirmations value. Must be a positive integer. - ' . $confirmations_value );
			}
		} elseif ( is_string( $confirmations_value ) ) {
			// 許可するタグ一覧。現在は'latest'のみ
			$valid_confirmations_values = array( 'latest' );

			if ( ! in_array( $confirmations_value, $valid_confirmations_values, true ) ) {
				throw new \InvalidArgumentException( '[6EED5EF3] Invalid confirmations value. - ' . $confirmations_value );
			}
		} else {
			throw new \InvalidArgumentException( '[A998D08F] Invalid confirmations value type. Must be int or string. - ' . gettype( $confirmations_value ) );
		}
	}
}
