<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

class I18nText {
	/**
	 * プラグイン名を取得します。
	 */
	public function pluginName(): string {
		return __( 'Todo List', 'todo-list' );
	}
}
