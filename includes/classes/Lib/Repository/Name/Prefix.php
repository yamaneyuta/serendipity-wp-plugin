<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Name;

use Cornix\Serendipity\Core\Lib\Repository\PluginInfo;

class Prefix {
	/**
	 * 本プラグインで使用するテーブル名のプレフィックスを取得します。
	 */
	public function tableName(): string {
		$text_domain = ( new PluginInfo() )->textDomain();
		return "${text_domain}_";
	}

	/**
	 * Cronに登録するアクション名に付与するプレフィックスを取得します。
	 */
	public function cronActionName(): string {
		$text_domain = ( new PluginInfo() )->textDomain();
		return "${text_domain}_";
	}

	/**
	 * optionsテーブルに格納する際のキー名に付与するプレフィックスを取得します。
	 */
	public function optionKeyName(): string {
		// 本プラグイン用のテーブルに付与するプレフィックスと同じものをoptionsのキーとして使用する
		return $this->tableName();
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
