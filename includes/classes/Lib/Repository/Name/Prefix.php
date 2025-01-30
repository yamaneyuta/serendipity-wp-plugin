<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Name;

use Cornix\Serendipity\Core\Lib\Repository\PluginInfo;

class Prefix {
	/**
	 * 本プラグインで使用するテーブル名のプレフィックスを取得します。
	 */
	public function tableName(): string {
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		$text_domain  = ( new PluginInfo() )->textDomain();
		return "${table_prefix}${text_domain}_";
	}

	/**
	 * Cronに登録するアクション名に付与するプレフィックスを取得します。
	 */
	public function cronActionName(): string {
		// optionsテーブルに格納する際のキー名のプレフィックスと同じものをcronのアクション名として使用する
		return $this->optionKeyName();
	}

	/**
	 * optionsテーブルに格納する際のキー名に付与するプレフィックスを取得します。
	 */
	public function optionKeyName(): string {
		$text_domain = ( new PluginInfo() )->textDomain();
		return "${text_domain}_";
	}

	/**
	 * transient(optionsテーブルの一時データ)として格納する際のキー名に付与するプレフィックスを取得します。
	 *
	 * @return string
	 */
	public function transientKeyName(): string {
		// optionsテーブルに格納する際のキー名のプレフィックスと同じものをtransientのキーとして使用する
		return $this->optionKeyName();
	}
}
