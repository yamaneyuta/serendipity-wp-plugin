<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Utils;

use Cornix\Serendipity\Core\Env\Env;

class LocalPath {

	/**
	 * このプラグインフォルダ内のパスをルートからのパスで返します。
	 *
	 * このプラグインが格納されるディレクトリ名が`wordpress-plugin`、引数の$pathが`package.json`の場合、
	 * 戻り値は`/var/www/html/wp-content/plugins/wordpress-plugin/package.json`となる。
	 *
	 * @param string $path このプラグインが格納されているディレクトリからの相対パス。
	 * @return string
	 */
	public static function get( string $path ): string {
		return wp_normalize_path(
			trailingslashit( trailingslashit( WP_PLUGIN_DIR ) . explode( '/', plugin_basename( __FILE__ ) )[0] ) . $path
		);
	}

	/**
	 * 利用規約のHTMLファイルが格納されているパスを返します。
	 *
	 * @return string
	 */
	public static function getTermsHtmlFilePath(): string {
		return self::get( 'includes/assets/docs/terms.html' );
	}

	/**
	 * 後からインストールサードパーティ製ライブラリ等を格納するディレクトリのパスを返します。
	 *
	 * @return string
	 */
	public static function getUserAssetsDir(): string {
		$dir_name = Constants::get( 'dirName.userAssets' );
		if ( Env::isDevelopmentMode() ) {
			return self::get( '.work-www/' . $dir_name );
		} else {
			return self::get( '../' . $dir_name );
		}
	}

	/**
	 * 後からインストールしたライブラリのautoload.phpのパスを返します。
	 *
	 * @return string
	 */
	public static function getDelayInstalledAutoloadPath(): string {
		return trailingslashit( self::getUserAssetsDir() ) . '/vendor/autoload.php';
	}

	/**
	 * メインコントラクトのメタデータが格納されているパスを返します。
	 *
	 * @return string
	 */
	public static function getMainContractMetaDataPath(): string {
		return self::get( 'includes/assets/contracts/Serendipity.json' );
	}

	public static function getViewContractMetaDataPath(): string {
		return self::get( 'includes/assets/contracts/SerendipityViewer.json' );
	}
}
