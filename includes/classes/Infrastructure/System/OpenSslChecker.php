<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\System;

class OpenSslChecker {
	/**
	 * OpenSSLが有効かどうかをチェックします。
	 *
	 * @return bool OpenSSLが有効な場合はtrue、無効な場合はfalseを返します。
	 */
	public function isExtensionLoaded(): bool {
		// OpenSSLの拡張が有効かどうかを確認
		return extension_loaded( 'openssl' );
	}

	/**
	 * 指定された暗号がサポートされているかどうかを確認します。
	 *
	 * @param string $cipher_name チェックする暗号の名前(例: 'aes-256-cbc')
	 * @return bool サポートされている場合はtrue、サポートされていない場合はfalseを返します。
	 */
	public function isSupportCipher( string $cipher_name ): bool {
		// 指定された暗号がサポートされているかどうかを確認
		return $this->isExtensionLoaded() && in_array( strtolower( $cipher_name ), openssl_get_cipher_methods(), true );
	}
}
