<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Rest;

use Cornix\Serendipity\Core\Lib\SystemInfo\Config;

class RestProperty {

	public function namespace(): string {
		// 名前空間はプラグインのテキストドメインを使用
		// 外部サイトなど、第三者からのアクセスは想定していないためバージョニングは行わない
		return ( new Config() )->getPluginInfo( 'TextDomain' );
	}

	public function graphQLRoute(): string {
		return '/graphql';
	}
}
