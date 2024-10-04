<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\Option;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class OptionFactory {

	/**
	 * Optionオブジェクトを生成します。
	 */
	private function createOption( string $raw_option_key_name ): Option {
		return new Option( ( new Prefix() )->optionKeyName() . $raw_option_key_name );
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
	 * 指定したネットワークカテゴリで、購入可能なチェーン一覧を取得または保存するオブジェクトを取得します。
	 */
	public function purchasableChainIDs( NetworkCategory $network_category ): Option {
		return $this->createOption( 'purchasable_chain_ids_' . $network_category->id() );
	}
}
