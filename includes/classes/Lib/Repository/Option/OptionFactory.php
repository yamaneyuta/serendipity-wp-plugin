<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\Repository\Name\Prefix;

class OptionFactory {

	/**
	 * optionsテーブルに問い合わせる時のキーを取得します。
	 */
	private function getOptionKeyName( string $raw_option_key_name ): string {
		return ( new Prefix() )->optionKeyName() . $raw_option_key_name;
	}

	/**
	 * 本プラグインがインストールされた時のバージョンを取得または保存するオブジェクトを取得します。
	 */
	public function lastInstalledPluginVersion(): StringOption {
		return new StringOption( $this->getOptionKeyName( 'last_installed_plugin_version' ) );
	}

	/**
	 * 本プラグインが使用する署名用ウォレットの秘密鍵を取得または保存するオブジェクトを取得します。
	 */
	public function signerPrivateKey(): ArrayOption {
		return new ArrayOption( $this->getOptionKeyName( 'signer_private_key' ) );
	}

	/**
	 * 指定したチェーンIDで、購入者が支払可能なトークンアドレス一覧を取得または保存するオブジェクトを取得します。
	 */
	public function payableTokenAddresses( int $chain_ID ): ArrayOption {
		return new ArrayOption( $this->getOptionKeyName( 'payable_token_addresses_' . $chain_ID ) );
	}

	/**
	 * 本プラグインが開発モードで動作しているかどうかを取得または保存するオブジェクトを取得します。
	 */
	public function isDevelopmentMode(): BoolOption {
		return new BoolOption( $this->getOptionKeyName( 'is_development_mode' ) );
	}

	/**
	 * 販売者が同意した利用規約に関する情報を保存する際のキーのプレフィックスを取得します。
	 */
	private function sellerAgreedTermsKeyPrefix(): string {
		return 'seller_agreed_terms_';
	}

	/**
	 * 販売者が同意した利用規約のバージョンを取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsVersion(): IntOption {
		$prefix = $this->sellerAgreedTermsKeyPrefix();
		return new IntOption( $this->getOptionKeyName( $prefix . 'version' ) );
	}

	/**
	 * 販売者が利用規約に同意した際の署名を取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsSignature(): StringOption {
		$prefix = $this->sellerAgreedTermsKeyPrefix();
		return new StringOption( $this->getOptionKeyName( $prefix . 'signature' ) );
	}

	/**
	 * 販売者が利用規約に同意した際のユーザーIDを取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsUserID(): IntOption {
		$prefix = $this->sellerAgreedTermsKeyPrefix();
		return new IntOption( $this->getOptionKeyName( $prefix . 'user_id' ) );
	}
}
