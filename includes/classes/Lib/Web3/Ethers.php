<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Strings\Strings;
use Elliptic\EC;
use kornrunner\Keccak;

class Ethers {

	public static function zeroAddress(): string {
		return '0x0000000000000000000000000000000000000000';
	}

	/**
	 * メッセージ及び署名からウォレットアドレスを取得します。
	 *
	 * @see https://github.com/simplito/elliptic-php?tab=readme-ov-file#verifying-ethereum-signature
	 */
	public static function verifyMessage( string $message, string $signature ): ?string {

		$message_hash = Keccak::hash( self::eip191( $message ), 256 );
		$sign         = array(
			'r' => substr( $signature, 2, 64 ),
			's' => substr( $signature, 66, 64 ),
		);
		$recid        = ord( hex2bin( substr( $signature, 130, 2 ) ) ) - 27;
		if ( $recid != ( $recid & 1 ) ) {
			return null;
		}

		$ec         = new EC( 'secp256k1' );
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
	public static function computeAddress( $public_key ): string {
		return '0x' . self::checksum( substr( Keccak::hash( substr( hex2bin( $public_key->encode( 'hex' ) ), 1 ), 256 ), 24 ) );
	}


	/**
	 * ウォレットアドレスにチェックサムを付与します。
	 */
	private static function checksum( string $address ): string {
		assert( false === Strings::strpos( $address, '0x' ) );

		$hash   = Keccak::hash( $address, 256 );
		$result = '';

		$len = strlen( $address );
		for ( $i = 0; $i < $len; $i++ ) {
			$result .= hexdec( $hash[ $i ] ) > 7 ? strtoupper( $address[ $i ] ) : $address[ $i ];
		}

		return $result;
	}
}
