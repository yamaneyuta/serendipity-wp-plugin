<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Name;

class CronActionName {

	private static function getPrefix(): string {
		return ( new Prefix() )->cronActionName();
	}

	/**
	 * Appコントラクトのクロール処理を行うCronアクション名を取得します。
	 */
	public static function appContractCrawl(): string {
		return self::getPrefix() . 'app_contract_crawl';
	}
}
