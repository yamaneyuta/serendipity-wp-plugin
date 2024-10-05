<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Name;

use Cornix\Serendipity\Core\Lib\Repository\PluginInfo;

class Slug {
	public function __construct() {
		$this->text_domain = ( new PluginInfo() )->textDomain();
	}
	private string $text_domain;

	/**
	 * 管理画面メニューのルートで使用するスラッグを取得します。
	 */
	public function adminMenuRoot(): string {
		return $this->text_domain;
	}
}
