<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Rest;

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;
use Cornix\Serendipity\Core\Lib\SystemInfo\WPSettings;

class RestProperty {

	public function namespace(): string {
		// 名前空間はプラグインのテキストドメインを使用
		// 外部サイトなど、第三者からのアクセスは想定していないためバージョニングは行わない
		return ( new PluginInfo() )->textDomain();
	}

	public function graphQLRoute(): string {
		return '/graphql';
	}

	/**
	 * GraphQLのURLを取得します。
	 * ※末尾にスラッシュは含まれません。
	 *
	 * @return string
	 */
	public function graphQlURL(): string {
		// パーマリンク構造が基本の場合は、`/wp-json/`を含むURLではアクセスできないので`?rest_route=`を含むURLでAPIアクセスを行う。
		// 参考: https://labor.ewigleere.net/2021/11/06/wordpress-restapi-404notfound-permalink-basic/

		$wp_settings   = new WPSettings();
		$api_root_path = $wp_settings->isDefaultPermalink() ? '/index.php?rest_route=/' : '/wp-json/';

		return untrailingslashit( $wp_settings->siteAddress() ) . $api_root_path . $this->namespace() . $this->graphQLRoute();
	}
}
