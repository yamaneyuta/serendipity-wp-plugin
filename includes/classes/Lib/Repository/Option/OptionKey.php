<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

final class OptionKey {

	/**
	 * データベーススキーマバージョンを保存するオプションキー
	 *
	 * @deprecated データベースのスキーマバージョンは管理しない。プラグインのバージョンで管理する。
	 */
	public const DB_SCHEMA_VERSION = 'db_schema_version';

	/**
	 * プラグインの設定を保存するオプションキーのプレフィックスを取得します。
	 *
	 * @deprecated TODO: OptionPrefixクラスを作成し、このメソッドを移動する
	 */
	public static function prefix(): string {
		$prefix = ( new PluginInfo() )->optionNamePrefix();
		assert( ! empty( $prefix ) );
		return $prefix;
	}
}
