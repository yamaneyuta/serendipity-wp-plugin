<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Database;

use Cornix\Serendipity\Core\Utils\Constants;

class TableName {
	/** @var string */
	private $db_prefix;

	/** @var string */
	private $table_name_prefix;

	/** @var TableName */
	private static $_instance;

	private function __construct() {
		$this->db_prefix         = $GLOBALS['wpdb']->prefix;
		$this->table_name_prefix = Constants::get( 'database.tableNamePrefix' );
	}

	private static function get_instance(): TableName {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/** wp_postsテーブルの名前を取得します。 */
	public static function getPostsTableName(): string {
		return self::get_instance()->db_prefix . 'posts';
	}

	private function get( string $name ): string {
		return $this->db_prefix . $this->table_name_prefix . $name;
	}

	public static function getHistorySettingPostTableName(): string {
		return self::get_instance()->get( 'hist_set_post' );
	}
	public static function getHistoryTicketsTableName(): string {
		return self::get_instance()->get( 'hist_tickets' );
	}
	public static function getHistoryPurchaseEventsTableName(): string {
		return self::get_instance()->get( 'hist_purchase_events' );
	}
	public static function getLogsTableName(): string {
		return self::get_instance()->get( 'hist_logs' );
	}
}
