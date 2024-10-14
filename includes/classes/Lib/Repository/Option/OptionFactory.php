<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Types\NetworkCategory;

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
	 * 指定したネットワークカテゴリで、購入者が支払可能なチェーン一覧を取得または保存するオブジェクトを取得します。
	 */
	public function payableChainIDs( NetworkCategory $network_category ): ArrayOption {
		return new ArrayOption( $this->getOptionKeyName( 'payable_chain_ids_' . $network_category->id() ) );
	}

	/**
	 * 指定したチェーンIDで、購入者が支払可能なトークン一覧を取得または保存するオブジェクトを取得します。
	 */
	public function payableSymbols( int $chain_ID ): ArrayOption {
		return new ArrayOption( $this->getOptionKeyName( 'payable_symbols_' . $chain_ID ) );
	}

	/**
	 * 本プラグインが開発モードで動作しているかどうかを取得または保存するオブジェクトを取得します。
	 */
	public function isDevelopmentMode(): BoolOption {
		return new BoolOption( $this->getOptionKeyName( 'is_development_mode' ) );
	}

	/**
	 * 販売者が同意した利用規約のバージョンを取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsVersion(): IntOption {
		return new IntOption( $this->getOptionKeyName( 'seller_agreed_terms_version' ) );
	}

	/**
	 * 販売者が利用規約に同意した際の署名を取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsSignature(): StringOption {
		return new StringOption( $this->getOptionKeyName( 'seller_agreed_terms_signature' ) );
	}

	/**
	 * 販売者が利用規約に同意した際のユーザーIDを取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsUserID(): IntOption {
		return new IntOption( $this->getOptionKeyName( 'seller_agreed_terms_user_id' ) );
	}
}
