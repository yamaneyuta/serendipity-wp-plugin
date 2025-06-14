<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Option;

use Cornix\Serendipity\Core\Repository\Name\Prefix;
use Cornix\Serendipity\Core\ValueObject\ChainID;

/** @deprecated */
class OptionFactory {

	/**
	 * optionsテーブルに問い合わせる時のキーを取得します。
	 */
	private function getOptionKeyName( string $raw_option_key_name ): string {
		return ( new Prefix() )->optionKeyPrefix() . $raw_option_key_name;
	}

	/**
	 * 指定されたチェーンが最初に有効になった(≒取引が開始された)ブロック番号を取得または保存するオブジェクトを取得します。
	 */
	public function activeSinceBlockNumberHex( ChainID $chain_ID ): StringOption {
		return new StringOption( $this->getOptionKeyName( 'active_since_block_number_hex_' . $chain_ID->value() ) );
	}

	/**
	 * 指定されたチェーン、ブロックタグで最後にクロールしたブロック番号を取得または保存するオブジェクトを取得します。
	 */
	public function crawledBlockNumberHex( ChainID $chain_ID, string $block_tag ): StringOption {
		return new StringOption( $this->getOptionKeyName( 'crawled_block_number_hex_' . $block_tag . '_' . $chain_ID->value() ) );
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
