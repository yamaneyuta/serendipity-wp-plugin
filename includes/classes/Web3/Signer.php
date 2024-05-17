<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Web3;

use Elliptic\EC;
use kornrunner\Keccak;
use Elliptic\EC\KeyPair;

/**
 * 署名関連の処理を行うクラス。
 */
class Signer {

	private function __construct( KeyPair $key_pair ) {
		$this->key_pair = $key_pair;
	}

	/** @var KeyPair */
	private $key_pair;

	/**
	 * メッセージに署名します。
	 */
	public function sign( $message ): string {

		$message_hash = self::getMessageHash( $message );

		$signature = $this->key_pair->sign( $message_hash, array( 'canonical' => true ) );

		$r = str_pad( $signature->r->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$s = str_pad( $signature->s->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$v = dechex( $signature->recoveryParam + 27 );

		$signature = "0x$r$s$v";

		return $signature;
	}

	/**
	 * ウォレットアドレスを取得します。
	 */
	public function getAddress(): string {
		return self::publicKeyToAddress( $this->key_pair->getPublic() );
	}

	/**
	 * 新しくSignerを作成します。
	 */
	public static function create(): Signer {
		$ec       = new EC( 'secp256k1' );
		$key_pair = $ec->genKeyPair();

		return new Signer( $key_pair );
	}

	/**
	 * PrivateKeyからSignerを作成します。
	 */
	public static function fromPrivateKey( string $private_key ): Signer {
		$ec       = new EC( 'secp256k1' );
		$key_pair = $ec->keyFromPrivate( $private_key );

		return new Signer( $key_pair );
	}

	/**
	 * 秘密鍵を取得します。
	 */
	public function getPrivateKey(): string {
		return $this->key_pair->getPrivate( 'hex' );
	}

	// /**
	// * 新しくウォレットの秘密鍵を作成します。
	// *
	// * 16進数のプレフィックス(`0x`)は付かない。
	// */
	// public static function create_private_key() : string {
	// $ec = new EC( 'secp256k1' );
	// return $ec->genKeyPair()->getPrivate( 'hex' );
	// }

	/**
	 * 署名を行うメッセージのハッシュ値を取得します。
	 */
	private static function getMessageHash( $message ): string {
		$message_length = strlen( $message );
		return Keccak::hash( "\x19Ethereum Signed Message:\n{$message_length}{$message}", 256 );
	}

	/**
	 * メッセージと署名から、署名を行ったウォレットアドレスを取得します。
	 */
	public static function getRecoverAccountId( $message, $signature ): ?string {

		$message_length = strlen( $message );
		$message_hash   = self::getMessageHash( $message );
		$sign           = array(
			'r' => substr( $signature, 2, 64 ),
			's' => substr( $signature, 66, 64 ),
		);
		$recid          = ord( hex2bin( substr( $signature, 130, 2 ) ) ) - 27;
		if ( $recid != ( $recid & 1 ) ) {
			return null;
		}

		$ec         = new EC( 'secp256k1' );
		$public_key = $ec->recoverPubKey( $message_hash, $sign, $recid );

		return self::publicKeyToAddress( $public_key );
	}


	/**
	 * 公開鍵からウォレットアドレスを取得します。
	 *
	 * @see https://github.com/simplito/elliptic-php#verifying-ethereum-signature
	 */
	private static function publicKeyToAddress( $public_key ): string {
		return '0x' . substr( Keccak::hash( substr( hex2bin( $public_key->encode( 'hex' ) ), 1 ), 256 ), 24 );
	}
}
