<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

class OptionKeyName {

	/**
	 * 本プラグインがインストールされたバージョンを取得または保存するためのオプション名を返します。
	 *
	 * @return string
	 */
	public function lastInstalledPluginVersion(): string {
		return 'last_installed_plugin_version';
	}
}
