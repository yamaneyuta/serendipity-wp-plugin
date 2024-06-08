<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Path;

class LocalPath {

	/**
	 * マシンのルートを起点とした、ファイルまたはディレクトリへのパスを取得します。
	 *
	 * @param string $this_project_path 本プロジェクトルートを起点とした、ファイルまたはディレクトリへのパス
	 * @return string
	 * @example
	 * LocalPath::get( '/src/block/index.js' ); // => '/var/www/html/wp-content/plugins/workspaces/src/block/index.js'
	 */
	public static function get( string $this_project_path ): string {
		return trailingslashit( self::getThisPluginDir() ) . $this_project_path;
	}

	/**
	 * 本プラグインがインストールされているディレクトリパスを取得します。
	 * 末尾のスラッシュは含まれません。
	 *
	 * @return string プラグインディレクトリパス(例: `/var/www/html/wp-content/plugins/workspaces`)
	 */
	private static function getThisPluginDir(): string {
		// WP_PLUGIN_DIR: WordPressのプラグインディレクトリパス。(例: `/var/www/html/wp-content/plugins`)
		// plugin_basename( __FILE__ ): WordPressのプラグインディレクトリからのパス。(例: `workspaces/includes/classes/Env/Path/LocalPath.php`)
		return trailingslashit( WP_PLUGIN_DIR ) . explode( '/', plugin_basename( __FILE__ ) )[0];
	}
}
