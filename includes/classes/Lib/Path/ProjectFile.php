<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Path;

class ProjectFile {

	public function __construct( string $path_from_plugin_dir ) {
		$this->path = $path_from_plugin_dir;
	}

	/** @var string */
	private $path;


	/**
	 * ブラウザからアクセスする際のURLに変換します。
	 */
	public function toUrl(): string {
		$full_path = $this->toLocalPath();
		assert( false !== realpath( $full_path ) );

		return trailingslashit( plugin_dir_url( $full_path ) ) . basename( $full_path );
	}


	/**
	 * マシンのルートを起点とした、ファイルまたはディレクトリへのパスを取得します。
	 * <code>
	 * ( new ProjectFile( '/src/block/index.js' ) )->toFullPath(); // => '/var/www/html/wp-content/plugins/workspaces/src/block/index.js'
	 * </code>
	 *
	 * @return string
	 */
	public function toLocalPath(): string {
		return trailingslashit( $this->getThisPluginDir() ) . $this->path;
	}


	/**
	 * 本プラグインがインストールされているディレクトリパスを取得します。
	 * 末尾のスラッシュは含まれません。
	 *
	 * @return string プラグインディレクトリパス(例: `/var/www/html/wp-content/plugins/workspaces`)
	 */
	private function getThisPluginDir(): string {
		// WP_PLUGIN_DIR: WordPressのプラグインディレクトリパス。(例: `/var/www/html/wp-content/plugins`)
		// plugin_basename( __FILE__ ): WordPressのプラグインディレクトリからのパス。(例: `workspaces/includes/classes/Env/Path/LocalPath.php`)
		return trailingslashit( WP_PLUGIN_DIR ) . explode( '/', plugin_basename( __FILE__ ) )[0];
	}
}
