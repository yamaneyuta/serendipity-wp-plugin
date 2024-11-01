<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class NetworkCategoryDefinitionTest extends WP_UnitTestCase {
	/**
	 * 処理のテストではなく、実装漏れの確認を行うためのテスト
	 * ネットワークカテゴリに属するチェーンIDがすべて含まれているかどうかをチェックする
	 *
	 * @test
	 * @testdox [A251494A] NetworkCategoryDefinition::getAllChainID
	 */
	public function getAllChainID() {
		// ARRANGE
		$all_chainIDs = ( new TestAllChainID() )->get();

		// 全てのネットワークカテゴリを取得
		$all_network_categories = NetworkCategory::all();

		// ACT
		// NetworkCategoryDefinitionクラスから、全ネットワークカテゴリのチェーンIDを取得
		/** @var int[] */
		$network_category_data_chainIDs = array();  // NetworkCategoryDefinitionクラスから取得したチェーンIDを格納する配列
		foreach ( $all_network_categories as $network_category ) {
			$network_category_data_chainIDs = array_merge( $network_category_data_chainIDs, ( new NetworkCategoryDefinition() )->getAllChainID( $network_category ) );
		}

		// ASSERT
		// ※ ここでエラーになる場合は`NetworkCategoryDefinition`内の定義を見直してください
		$this->assertEquals( $all_chainIDs, $network_category_data_chainIDs );
	}
}
