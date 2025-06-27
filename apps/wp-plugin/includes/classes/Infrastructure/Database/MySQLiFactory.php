<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database;

use mysqli;
use wpdb;

class MySQLiFactory {

	public function create( wpdb $wpdb ): mysqli {
		if ( $wpdb->dbh ) {
			return $wpdb->dbh;
		}

		assert( strlen( $wpdb->dbhost ) > 0 );
		assert( strlen( $wpdb->dbuser ) > 0 );
		assert( strlen( $wpdb->dbpassword ) > 0 );
		assert( strlen( $wpdb->dbname ) > 0 );

		// 起動直後の場合、接続出来ない場合があるため、最大10回リトライする。(CI環境用)
		for ( $i = 0; $i < 10; $i++ ) {
			try {
				$mysqli = new mysqli( $wpdb->dbhost, $wpdb->dbuser, $wpdb->dbpassword, $wpdb->dbname );
				return $mysqli;
			} catch ( \Throwable $e ) {
				error_log( '[5403F424]' . $e->getMessage() );
				sleep( 1 );
			}
		}

		throw new \RuntimeException( '[41143BCA] Failed to connect to MySQL after 10 attempts.' );
	}
}
