<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service;

use Cornix\Serendipity\Core\Entity\Signer;
use Cornix\Serendipity\Core\Infrastructure\System\OpenSslChecker;
use Cornix\Serendipity\Core\Repository\ServerSignerPrivateKeyRepository;

class ServerSignerService {

	public function __construct( ServerSignerPrivateKeyRepository $repository ) {
		$this->repository = $repository;
	}

	private ServerSignerPrivateKeyRepository $repository;
	private const CIPHER_ALGO       = 'AES-256-CBC';
	private const CIPHER_KEY_LENGTH = 32; // AES-256のキー長は32バイト

	/** 署名用ウォレットの初期化を行います */
	public function initializeServerSigner(): void {
		if ( ! is_null( $this->repository->privateKeyData() ) ) {
			// すでにウォレットの秘密鍵が保存されている場合はログ出力して処理抜け
			// TODO: ログ出力
			return;
		}

		// 新しくウォレットを生成
		$server_signer = Signer::generateRandom();

		/** @var null|string */
		$private_key_data = $server_signer->privateKey()->value();  // 保存する秘密鍵データ(平文/暗号化済み) ここでは一旦平文を設定
		/** @var null|string */
		$key = null;                // 暗号化キー(秘密鍵を暗号化する場合は値が設定される)
		/** @var null|string */
		$iv = null;                 // 暗号化初期化ベクトル(秘密鍵を暗号化する場合は値が設定される)

		// 暗号化して保存可能かどうかを取得
		$open_ssl_checker = new OpenSslChecker();
		$is_encryptable   = $open_ssl_checker->isExtensionLoaded() && $open_ssl_checker->isSupportCipher( self::CIPHER_ALGO );

		if ( $is_encryptable ) {
			// 暗号化可能な場合はopenSSLを使用して秘密鍵を暗号化
			$key       = openssl_random_pseudo_bytes( self::CIPHER_KEY_LENGTH );
			$iv_length = openssl_cipher_iv_length( self::CIPHER_ALGO );
			$iv        = openssl_random_pseudo_bytes( $iv_length );

			$private_key_data = openssl_encrypt( $private_key_data, self::CIPHER_ALGO, $key, OPENSSL_RAW_DATA, $iv );
			if ( false === $private_key_data ) {
				throw new \RuntimeException( '[81EEF940] Failed to encrypt private key data: ' . openssl_error_string() );
			}

			// 保存前に各値をbase64エンコード
			$private_key_data = base64_encode( $private_key_data );
			$key              = base64_encode( $key );
			$iv               = base64_encode( $iv );
		}

		// リポジトリに保存
		$this->repository->save(
			$server_signer->address(),
			$private_key_data,
			$key,
			$iv
		);
	}

	public function getServerSigner(): ?Signer {
		// 保存されている秘密鍵データを取得
		$private_key_data = $this->repository->privateKeyData();
		if ( is_null( $private_key_data ) ) {
			return null; // 秘密鍵が保存されていない場合はnullを返す
		}

		// 暗号化されている場合は復号化
		$encryption_key = $this->repository->encryptionKey();
		$encryption_iv  = $this->repository->encryptionIv();

		if ( ! is_null( $encryption_key ) && ! is_null( $encryption_iv ) ) {
			$private_key_data = base64_decode( $private_key_data );
			$encryption_key   = base64_decode( $encryption_key );
			$encryption_iv    = base64_decode( $encryption_iv );

			$private_key_data = openssl_decrypt( $private_key_data, self::CIPHER_ALGO, $encryption_key, OPENSSL_RAW_DATA, $encryption_iv );
			if ( false === $private_key_data ) {
				throw new \RuntimeException( '[ABF69851] Failed to decrypt private key data: ' . openssl_error_string() );
			}
		}

		$serverSigner = new Signer( $private_key_data );
		assert( $serverSigner->address()->equals( $this->repository->address() ), '[ED4952AA] Address mismatch between stored and generated signer.' );

		return $serverSigner;
	}
}
