<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Strings\Strings;
use Cornix\Serendipity\Core\ValueObject\Address;
use Elliptic\EC;
use kornrunner\Keccak;

class Ethers {

	public static function zeroAddress(): Address {
		return new Address( '0x0000000000000000000000000000000000000000' );
	}

	/**
	 * メッセージ及び署名からウォレットアドレスを取得します。
	 *
	 * @see https://github.com/simplito/elliptic-php?tab=readme-ov-file#verifying-ethereum-signature
	 */
	public static function verifyMessage( string $message, string $signature ): ?Address {

		$message_hash = Keccak::hash( self::eip191( $message ), 256 );
		$sign         = array(
			'r' => substr( $signature, 2, 64 ),
			's' => substr( $signature, 66, 64 ),
		);
		$recid        = ord( hex2bin( substr( $signature, 130, 2 ) ) ) - 27;
		if ( $recid != ( $recid & 1 ) ) {
			return null;
		}

		$ec = new EC( 'secp256k1' );
		/** @var \Elliptic\Curve\ShortCurve\Point */
		$public_key = $ec->recoverPubKey( $message_hash, $sign, $recid );

		return self::computeAddress( $public_key );
	}


	/**
	 * メッセージをEIP191に準拠した形式に変換します。
	 *
	 * @see https://eips.ethereum.org/EIPS/eip-191
	 */
	public static function eip191( string $message ): string {
		$message_length = strlen( $message );
		return "\x19Ethereum Signed Message:\n{$message_length}{$message}";
	}


	/**
	 * 公開鍵からウォレットアドレスを取得します。
	 *
	 * @see https://github.com/simplito/elliptic-php#verifying-ethereum-signature
	 */
	public static function computeAddress( \Elliptic\Curve\ShortCurve\Point $public_key ): Address {
		$address = \Web3\Utils::toChecksumAddress( substr( Keccak::hash( substr( hex2bin( $public_key->encode( 'hex' ) ), 1 ), 256 ), 24 ) );
		assert( self::isAddress( $address ), '[D9ADC5E3] Invalid address. ' . $address );
		return new Address( $address );
	}


	/**
	 * ウォレットアドレスにチェックサムを付与して返します。
	 */
	public static function getAddress( string $address ): string {
		$result = \Web3\Utils::toChecksumAddress( $address );
		assert( self::isAddress( $result ), '[A5954271] Invalid address. ' . $result );
		return $result;
	}

	/**
	 * 指定された文字列が正しいウォレットアドレスかどうかを判定します。
	 */
	public static function isAddress( string $address ): bool {
		// 本アプリでは`0x`プレフィックスを必須とする
		return Strings::starts_with( $address, '0x' ) && \Web3\Utils::isAddress( $address );
	}
}
