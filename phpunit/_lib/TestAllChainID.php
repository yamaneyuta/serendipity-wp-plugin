<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\ChainIDs;

/** ChainIDクラスに定義されているチェーンID一覧を取得するためのクラス */
class TestAllChainID {

	/**
	 * ChainIDクラスに定義されているチェーンID一覧を取得します。
	 *
	 * @return int[]
	 */
	public function get(): array {
		return ( new ChainIDs() )->get();
	}
}
