<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

class Prefix {
	/**
	 * 本プラグインで使用するテーブル名のプレフィックスを取得します。
	 */
	public function tableName(): string {
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
}
