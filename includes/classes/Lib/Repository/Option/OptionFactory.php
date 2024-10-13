<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class OptionFactory {

	/**
	 * Optionオブジェクトを生成します。
	 *
	 * @deprecated
	 */
	private function createOption( string $raw_option_key_name ): Option {
		return new Option( ( new Prefix() )->optionKeyName() . $raw_option_key_name );
	}

	/**
	 * optionsテーブルに問い合わせる時のキーを取得します。
	 */
	private function getOptionKeyName( string $raw_option_key_name ): string {
		return ( new Prefix() )->optionKeyName() . $raw_option_key_name;
	}

	/**
	 * 本プラグインがインストールされた時のバージョンを取得または保存するオブジェクトを取得します。
	 */
	public function lastInstalledPluginVersion(): Option {
		return $this->createOption( 'last_installed_plugin_version' );
	}

	/**
	 * 本プラグインが使用する署名用ウォレットの秘密鍵を取得または保存するオブジェクトを取得します。
	 */
	public function signerPrivateKey(): Option {
		return $this->createOption( 'signer_private_key' );
	}

	/**
	 * 販売者の利用規約同意情報を取得または保存するオブジェクトを取得します。
	 */
	public function sellerTermsAgreedInfo(): Option {
		return $this->createOption( 'seller_terms_agreed_info' );
	}

	/**
	 * 指定したネットワークカテゴリで、購入者が支払可能なチェーン一覧を取得または保存するオブジェクトを取得します。
	 */
	public function payableChainIDs( NetworkCategory $network_category ): Option {
		return $this->createOption( 'payable_chain_ids_' . $network_category->id() );
	}

	/**
	 * 指定したチェーンIDで、購入者が支払可能なトークン一覧を取得または保存するオブジェクトを取得します。
	 */
	public function payableSymbols( int $chain_ID ): Option {
		return $this->createOption( 'payable_symbols_' . $chain_ID );
	}

	/**
	 * 本プラグインが開発モードで動作しているかどうかを取得または保存するオブジェクトを取得します。
	 */
	public function isDevelopmentMode(): Option {
		return $this->createOption( 'is_development_mode' );
	}

	/**
	 * 販売者が同意した利用規約のバージョンを取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsVersion(): IntOption {
		return new IntOption( $this->getOptionKeyName( 'seller_agreed_terms_version' ) );
	}

	/**
	 * 販売者が署名した同意メッセージを取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsMessage(): StringOption {
		return new StringOption( $this->getOptionKeyName( 'seller_agreed_terms_message' ) );
	}

	/**
	 * 販売者が利用規約に同意した際の署名を取得または保存するオブジェクトを取得します。
	 */
	public function sellerAgreedTermsSignature(): StringOption {
		return new StringOption( $this->getOptionKeyName( 'seller_agreed_terms_signature' ) );
	}
}
