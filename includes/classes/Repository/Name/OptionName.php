<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Name;

use Cornix\Serendipity\Core\Repository\Name\Prefix;

class OptionName {

	/**
	 * 指定されたオプション名に接頭辞をつけて返します
	 * 作成するオプション名はこのメソッドを使用してください
	 */
	private function addPrefix( string $option_name ): string {
		return ( new Prefix() )->optionKeyPrefix() . $option_name;
	}

	/**
	 * インストールされたプラグインのバージョン
	 */
	public function pluginVersion(): string {
		return $this->addPrefix( 'plugin_version' );
	}
}
