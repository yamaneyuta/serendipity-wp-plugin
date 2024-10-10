<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\Option;
use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * 管理者が設定した購入者が支払い可能なチェーンID一覧を取得または保存するクラス。
 */
class PayableChainIDs {

	/**
	 * optionsテーブルへデータを保存または取得するためのオブジェクトを取得します。
	 *
	 * @param NetworkCategory $network_category
	 * @return Option
	 */
	private function getOption( NetworkCategory $network_category ): Option {
		return ( new OptionFactory() )->payableChainIDs( $network_category );
	}

	/**
	 * 指定したネットワークカテゴリで購入可能なチェーンID一覧を取得します。
	 *
	 * @param NetworkCategory $network_category
	 * @return int[]
	 */
	public function get( NetworkCategory $network_category ): array {
		return $this->getOption( $network_category )->get( array() );
	}

	/**
	 * 指定したネットワークカテゴリで購入可能なチェーンID一覧を保存します。
	 *
	 * @param NetworkCategory $network_category
	 * @param int[]           $chain_ids
	 */
	public function save( NetworkCategory $network_category, array $chain_ids ): void {
		// 引数チェック
		$this->checkNetworkCategory( $network_category, $chain_ids );

		// 保存
		$this->getOption( $network_category )->update( $chain_ids );
	}

	/**
	 * 指定したチェーンIDの一覧がすべて指定したネットワークカテゴリに含まれるかどうかをチェックします。
	 * 対象のネットワークカテゴリに含まれないチェーンIDが見つかった場合は例外をスローします。
	 *
	 * @param NetworkCategory $network_category
	 * @param int[]           $chain_ids
	 */
	private function checkNetworkCategory( NetworkCategory $network_category, array $chain_ids ): void {
		$all_chain_ids = ( new ChainData() )->getAllChainID( $network_category );
		foreach ( $chain_ids as $chain_id ) {
			if ( ! in_array( $chain_id, $all_chain_ids, true ) ) {
				throw new \InvalidArgumentException( '[16E118C3] Invalid chain ID: ' . $chain_id );
			}
		}
	}
}
