<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\SystemInfo;

use Cornix\Serendipity\Core\Lib\Rest\RestProperty;

/**
 * インストールされている環境から情報を取得するクラス。
 * マシンに配置されているファイルやインストール済みのデータベースなど、実行環境によって異なる情報を取得する場合に使用します。
 */
class Environment {

	/**
	 * 本プラグインが使用するREST APIのルートURLを取得します。(namespaceを含む)
	 * ※末尾にスラッシュは含まれません。
	 *
	 * @return string
	 */
	public function restRootUrl(): string {
		// パーマリンク構造が基本の場合は、`/wp-json/`を含むURLではアクセスできないので`?rest_route=`を含むURLでAPIアクセスを行う。
		// 参考: https://labor.ewigleere.net/2021/11/06/wordpress-restapi-404notfound-permalink-basic/
		$is_default_permalink = $this->isDefaultPermalink();
		$api_root_path        = $is_default_permalink ? '/index.php?rest_route=/' : '/wp-json/';

		return untrailingslashit( $this->siteAddress() ) . $api_root_path . ( new RestProperty() )->namespace();
	}

	/**
	 * 「設定 > パーマリンク設定」で「基本」(英語の場合は「Plain」)のパーマリンクが選択されているかどうかを取得します。
	 *
	 * @return bool
	 */
	private function isDefaultPermalink(): bool {
		return get_option( 'permalink_structure' ) === '';
	}


	/**
	 * 「設定 > 一般」の「サイトアドレス (URL)」(サイト訪問者がアクセスするURL)を返します。
	 */
	private function siteAddress(): string {
		// get_bloginfo('url') calls home_url() calls get_home_url()
		// https://wordpress.stackexchange.com/questions/16161/what-is-difference-between-get-bloginfourl-and-get-site-url
		return get_home_url();
	}
}
