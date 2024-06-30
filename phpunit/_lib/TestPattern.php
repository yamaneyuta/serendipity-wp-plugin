<?php
declare(strict_types=1);

class TestPattern {

	/**
	 * テストパターンに対して、各データベースの接続先のパターンを掛け合わせたテストパターンを生成します。
	 * 引数のテストパターンが2、データベースが3つの場合、6つのテストパターンが生成されます。
	 * 新しく生成したテストパターン(戻り値)の最後に、データベースのホスト名を追加します。
	 *
	 * 例:
	 * $test_data_list = [ [ 1, true ], [ 2, false ] ]
	 * $hosts = [ 'mysql1', 'mysql2', 'mysql3' ]
	 * 生成されるテストパターン = [
	 *     [ 1, true, 'mysql1' ],
	 *     [ 1, true, 'mysql2' ],
	 *     [ 1, true, 'mysql3' ],
	 *     [ 2, false, 'mysql1' ],
	 *     [ 2, false, 'mysql2' ],
	 *     [ 2, false, 'mysql3' ]
	 * ]
	 *
	 * @param array $test_data_list テストパターン(1つのDBに対するテストパターン)
	 * @return array テストパターン(全てのDBに対するテストパターン)
	 */
	public function createDBHostMatrix( array $test_data_list = null ): array {

		$test_data_list = is_null( $test_data_list ) ? array( array() ) : $test_data_list;

		$hosts = ( new TestDBHosts() )->get();

		$result = array();
		foreach ( $test_data_list as $test_data ) {
			// $test_data は、[ 1, true ]のような配列
			foreach ( $hosts as $host ) {
				$new_test_data = array( ...$test_data, $host );
				$result[]      = $new_test_data;
			}
		}

		return $result;
	}
}
