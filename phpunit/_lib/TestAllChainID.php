<?php
declare(strict_types=1);

/** ChainIDクラスに定義されているチェーンID一覧を取得するためのクラス */
class TestAllChainID {

	/**
	 * ChainIDクラスに定義されているチェーンID一覧を取得します。
	 *
	 * @return int[]
	 */
	public function get(): array {
		$reflectionClass = new \ReflectionClass( 'Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID' );
		$constants       = $reflectionClass->getConstants();
		/** @var int[] */
		$all_chainIDs = array_values( $constants );
		return $all_chainIDs;
	}
}
