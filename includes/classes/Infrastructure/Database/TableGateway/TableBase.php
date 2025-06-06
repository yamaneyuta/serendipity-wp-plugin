<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Infrastructure\Database\Util\NamedPlaceholder;

abstract class TableBase implements ITable {
	public function __construct( \wpdb $wpdb, string $table_name ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name;
	}
	private \wpdb $wpdb;
	private string $table_name;
	private ?\mysqli $mysqli_cache = null;

	protected function wpdb(): \wpdb {
		return $this->wpdb;
	}

	protected function tableName(): string {
		return $this->table_name;
	}

	protected function mysqli(): \mysqli {
		if ( is_null( $this->mysqli_cache ) ) {
			$this->mysqli_cache = ( new MySQLiFactory() )->create( $this->wpdb );
		}
		return $this->mysqli_cache;
	}

	/**
	 * Named placeholder を使用して SQL クエリを構築します
	 * ※プレースホルダは、キーがコロンで始まる形式（例: `:key`）で指定してください。
	 *
	 * @param string               $query
	 * @param array<string,string> $args プレースホルダに対応する値の連想配列
	 */
	protected function namedPrepare( string $query, array $args ): string {
		return ( new NamedPlaceholder( $this->wpdb ) )->prepare( $query, $args );
	}


	/** @inheritdoc */
	abstract public function create(): void;

	/** @inheritdoc */
	public function drop(): void {
		$sql    = "DROP TABLE IF EXISTS `{$this->tableName()}`;";
		$result = $this->mysqli()->query( $sql );
		assert( true === $result, '[6948871B] Failed to drop table: ' . $this->tableName() );
	}
}
