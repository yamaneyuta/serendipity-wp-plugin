<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Name;

use Cornix\Serendipity\Core\Lib\Repository\PluginInfo;

class Prefix {

	/**
	 * プレフィックスとして使用しやすいように変換したテキストドメインを取得します。
	 *
	 * @return string
	 */
	private function convertedTextDomain(): string {
		$text_domain = ( new PluginInfo() )->textDomain();

		// プラグインのテキストドメインのハイフンをアンダーバーに変換
		$result = str_replace( '-', '_', $text_domain );

		// 結果はアンダーバーと小文字の英字のみ(数字、ハイフンは除外)
		assert( preg_match( '/^[a-z_]+$/', $result ) === 1, "[CBE2850E] Invalid format - '{$text_domain}'" );

		return $result;
	}

	/**
	 * 本プラグインで使用するテーブル名のプレフィックスを取得します。
	 */
	public function tableNamePrefix(): string {
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		$text_domain  = $this->convertedTextDomain();
		return "${table_prefix}${text_domain}_";
	}

	/**
	 * Cronに登録するアクション名に付与するプレフィックスを取得します。
	 */
	public function cronActionNamePrefix(): string {
		$text_domain = $this->convertedTextDomain();
		return "${text_domain}_";
	}

	/**
	 * optionsテーブルに格納する際のキー名に付与するプレフィックスを取得します。
	 */
	public function optionKeyPrefix(): string {
		$text_domain = $this->convertedTextDomain();
		return "${text_domain}_";
	}

	/**
	 * transient(optionsテーブルの一時データ)として格納する際のキー名に付与するプレフィックスを取得します。
	 *
	 * @return string
	 */
	public function transientKeyPrefix(): string {
		$text_domain = $this->convertedTextDomain();
		return "${text_domain}_";
	}
}
