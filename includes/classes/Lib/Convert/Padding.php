<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Convert;

use Cornix\Serendipity\Core\Lib\Security\Validate;

class Padding {
	/**
	 * 16進数の値を32バイトの16進数に変換します。
	 * 用途: イベントのtopics等
	 */
	public function toBytes32Hex( string $hex ): string {
		$hex = strtolower( $hex ); // アドレスが渡される可能性があるため、数値として扱うために小文字にする
		Validate::checkHex( $hex );
		assert( strlen( $hex ) <= 66 ); // 0x + 32バイトの16進数のため、66文字まで
		$hex = str_replace( '0x', '', $hex );
		$hex = str_pad( $hex, 64, '0', STR_PAD_LEFT );
		return strtolower( '0x' . $hex ); // 32バイトの16進数のため、すべて小文字にして返す
	}
}
