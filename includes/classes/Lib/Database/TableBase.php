<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database;

abstract class TableBase implements ITable {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private \wpdb $wpdb;
	private ?\mysqli $mysqli_cache = null;

	protected function wpdb(): \wpdb {
		return $this->wpdb;
	}

	protected function mysqli(): \mysqli {
		if ( is_null( $this->mysqli_cache ) ) {
			$this->mysqli_cache = ( new MySQLiFactory() )->create( $this->wpdb );
		}
		return $this->mysqli_cache;
	}


	/** @inheritdoc */
	abstract public function create(): void;

	/** @inheritdoc */
	abstract public function drop(): void;
}
