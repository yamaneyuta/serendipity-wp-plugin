<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Option;

use Cornix\Serendipity\Core\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Types\RpcUrlProviderType;

class OptionFactory {

	/**
	 * optionsテーブルに問い合わせる時のキーを取得します。
	 */
	private function getOptionKeyName( string $raw_option_key_name ): string {
		return ( new Prefix() )->optionKeyPrefix() . $raw_option_key_name;
	}

	/**
	 * 本プラグインがインストールされた時のバージョンを取得または保存するオブジェクトを取得します。
	 */
	public function lastInstalledPluginVersion(): StringOption {
		return new StringOption( $this->getOptionKeyName( 'last_installed_plugin_version' ) );
	}

	/**
	 * 本プラグインが使用する署名用ウォレットの情報を取得または保存するオブジェクトを取得します。
	 */
	public function serverSignerData(): ArrayOption {
		return new ArrayOption( $this->getOptionKeyName( 'server_signer_data' ) );
	}

	/**
	 * 指定されたチェーンが最初に有効になった(≒取引が開始された)ブロック番号を取得または保存するオブジェクトを取得します。
	 */
	public function activeSinceBlockNumberHex( int $chain_ID ): StringOption {
		return new StringOption( $this->getOptionKeyName( 'active_since_block_number_hex_' . $chain_ID ) );
	}

	/**
	 * 指定されたチェーン、ブロックタグで最後にクロールしたブロック番号を取得または保存するオブジェクトを取得します。
	 */
	public function crawledBlockNumberHex( int $chain_ID, string $block_tag ): StringOption {
		return new StringOption( $this->getOptionKeyName( 'crawled_block_number_hex_' . $block_tag . '_' . $chain_ID ) );
	}

	/**
	 * 指定したチェーンIDで、購入者が支払可能なトークンアドレス一覧を取得または保存するオブジェクトを取得します。
	 */
	public function payableTokenAddresses( int $chain_ID ): ArrayOption {
		return new ArrayOption( $this->getOptionKeyName( 'payable_token_addresses_' . $chain_ID ) );
	}

	/**
	 * 指定したチェーンIDで、購入完了と判定するための待機ブロック数
	 * またはブロック位置('safe', 'finalized')を取得または保存するオブジェクトを取得します。
	 */
	public function confirmations( int $chain_ID ): StringOption {
		return new StringOption( $this->getOptionKeyName( 'confirmations_' . $chain_ID ) );
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

	/**
	 * ユーザーが設定したRPC URLを取得または保存するオブジェクトを取得します。
	 *
	 * @param int $chain_ID
	 */
	public function rpcUrl( int $chain_ID ): StringOption {
		return new StringOption( $this->getOptionKeyName( 'rpc_url_' . $chain_ID ) );
	}
}
