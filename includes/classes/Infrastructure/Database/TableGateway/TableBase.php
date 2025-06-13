<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\Database\Util\NamedPlaceholder;

abstract class TableBase {
	public function __construct( \wpdb $wpdb, string $table_name ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name;
	}
	private \wpdb $wpdb;
	private string $table_name;

	protected function wpdb(): \wpdb {
		return $this->wpdb;
	}

	protected function tableName(): string {
		return $this->table_name;
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
}
