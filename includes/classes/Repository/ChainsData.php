<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Constants\Config;
use Cornix\Serendipity\Core\Lib\Database\Table\ChainTable;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;

/**
 * 本プラグインで扱うチェーンの情報を取得するクラス
 */
class ChainsData {
	public function __construct() {
		$this->chain_table = new ChainTable();
	}

	private ChainTable $chain_table;

	private function records() {
		// フィルタ無しで全てのチェーン情報を取得
		return $this->chain_table->select();
	}

	/**
	 * 指定したネットワークカテゴリに属するチェーンID一覧を取得します
	 * ネットワークカテゴリを指定しない場合、すべてのチェーンIDを取得します。
	 * チェーンIDはチェーンテーブルに格納されているものが対象です。
	 *
	 * @return int[]
	 */
	public function chainIDs( NetworkCategory $network_category = null ): array {
		$chain_ids = array_values( array_map( fn ( $record ) => $record->chain_id, $this->records() ) );

		if ( ! is_null( $network_category ) ) {
			$chain_ids = array_values(
				array_filter(
					$chain_ids,
					fn ( $chain_id ) => Config::NETWORK_CATEGORIES[ $chain_id ] === $network_category->id()
				)
			);
		}

		return $chain_ids;
	}
}
