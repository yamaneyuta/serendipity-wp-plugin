<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

/**
 * javascriptの変数としてPHPから情報を渡す際の変数名を返すクラス。
 */
class PhpVarName {
	public function get(): string {
		// ※ TypeScript側と整合性を取ること
		return 'php_var_20792bdd';
	}
}
