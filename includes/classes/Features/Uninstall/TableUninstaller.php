<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Uninstall;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Lib\Strings\Strings;

class TableUninstaller {
	public function execute( \wpdb $wpdb ): void {
		$table_name_prefix = ( new Prefix() )->tableName();

		// $table_name_prefixで始まるテーブルをすべて削除する
		$sql = <<<SQL
			SHOW TABLES LIKE '{$table_name_prefix}%'
		SQL;

		$table_names = $wpdb->get_col( $sql );

		// $table_namesの数が正しいかどうかをチェック(CIの時は0となるため、0も許容)
		// ※テーブルが追加された時はここがエラーになるので値を修正すること
		assert( in_array( count( $table_names ), array( 0, 7 ), true ), '[449E8AD5] Invalid table names. table_names: ' . json_encode( $table_names ) );

		foreach ( $table_names as $table_name ) {
			assert( 0 === Strings::strpos( $table_name, $table_name_prefix ) );
			$mysqli = ( new MySQLiFactory() )->create( $wpdb );
			$result = $mysqli->query( "DROP TABLE IF EXISTS `$table_name`" );
			assert( false !== $result );
		}
	}
}
