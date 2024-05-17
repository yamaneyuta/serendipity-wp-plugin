<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Utils;

class Url {

	/**
	 * 指定したファイルまたはディレクトリのURLを返します。
	 *
	 * @param string $path このプラグインが格納されているディレクトリからの相対パス。
	 */
	public static function get( string $path ): string {
		$path     = untrailingslashit( $path );
		$filePath = LocalPath::get( $path );
		return trailingslashit( plugin_dir_url( $filePath ) ) . basename( $filePath );
	}


	/**
	 * 「設定 > 一般」の「サイトアドレス (URL)」(サイト訪問者がアクセスするURL)を返します。
	 */
	public static function getSiteAddress(): string {
		// get_bloginfo('url') calls home_url() calls get_home_url()
		// https://wordpress.stackexchange.com/questions/16161/what-is-difference-between-get-bloginfourl-and-get-site-url
		return get_home_url();
	}
}
