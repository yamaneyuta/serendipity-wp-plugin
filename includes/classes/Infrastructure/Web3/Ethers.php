<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\PrivateKey;
use Elliptic\EC;
use kornrunner\Keccak;

class Ethers {

	public static function zeroAddress(): Address {
		return new Address( '0x0000000000000000000000000000000000000000' );
	}

	/**
	 * EC/KeyPairに変換します
	 *
	 * @disregard P1009 Undefined type
	 */
	private static function signerPrivateKeyToEcKeyPair(
		#[\SensitiveParameter]
		PrivateKey $private_key
	): \Elliptic\EC\KeyPair {
		$ec = new EC( 'secp256k1' );
		return $ec->keyFromPrivate( $private_key->value() );
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
	 * 秘密鍵からアドレスを取得します
	 *
	 * @disregard P1009 Undefined type
	 */
	public static function privateKeyToAddress(
		#[\SensitiveParameter]
		PrivateKey $private_key
	): Address {
		$key_pair = self::signerPrivateKeyToEcKeyPair( $private_key );
		return self::computeAddress( $key_pair->getPublic() );
	}

	/**
	 * ランダムな秘密鍵を生成します。
	 */
	public static function generatePrivateKey(): PrivateKey {
		$ec       = new EC( 'secp256k1' );
		$key_pair = $ec->genKeyPair();
		return PrivateKey::from( $key_pair->getPrivate( 'hex' ) );
	}

	/**
	 * 公開鍵からウォレットアドレスを取得します。
	 *
	 * @see https://github.com/simplito/elliptic-php#verifying-ethereum-signature
	 */
	public static function computeAddress( \Elliptic\Curve\ShortCurve\Point $public_key ): Address {
		$address_value = \Web3\Utils::toChecksumAddress( substr( Keccak::hash( substr( hex2bin( $public_key->encode( 'hex' ) ), 1 ), 256 ), 24 ) );
		return new Address( $address_value );
	}

	/**
	 * メッセージに署名を行います。
	 *
	 * @see https://ethereum.stackexchange.com/a/86503
	 * @disregard P1009 Undefined type
	 */
	public static function signMessage(
		#[\SensitiveParameter]
		PrivateKey $private_key,
		string $message
	): string {
		$message_hash = Keccak::hash( self::eip191( $message ), 256 );

		$key_pair  = self::signerPrivateKeyToEcKeyPair( $private_key );
		$signature = $key_pair->sign( $message_hash, array( 'canonical' => true ) );

		$r = str_pad( $signature->r->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$s = str_pad( $signature->s->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$v = dechex( $signature->recoveryParam + 27 );

		$signature = "0x$r$s$v";

		return $signature;
	}
}
