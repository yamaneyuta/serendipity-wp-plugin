<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\TableGateway;

use Cornix\Serendipity\Core\Entity\Token;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\TableRecord\TokenTableRecord;

/**
 * トークンの情報を記録するテーブル
 */
class TokenTable extends TableBase {

	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->token() );
	}

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb()->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`     timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`     timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`       bigint        unsigned  NOT NULL,
				`address`        varchar(191)            NOT NULL,
				`symbol`         varchar(191)            NOT NULL,
				`decimals`       int                     NOT NULL,
				`is_payable`     boolean                 NOT NULL,
				PRIMARY KEY (`chain_id`, `address`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[] Failed to create token table. ' . $this->mysqli()->error );
		}
	}

	/**
	 * テーブルに保存されているトークンデータ一覧を取得します。
	 *
	 * @return TokenTableRecord[]
	 */
	public function all(): array {
		$sql = <<<SQL
			SELECT `chain_id`, `address`, `symbol`, `decimals`, `is_payable`
			FROM `{$this->tableName()}`
		SQL;

		$result = $this->wpdb()->get_results( $sql );
		if ( false === $result ) {
			throw new \Exception( '[CA8FE52D] Failed to get token data.' );
		}

		$records = array();
		foreach ( $result as $row ) {
			$row->chain_id   = (int) $row->chain_id;
			$row->address    = (string) $row->address;
			$row->symbol     = (string) $row->symbol;
			$row->decimals   = (int) $row->decimals;
			$row->is_payable = (bool) $row->is_payable;

			$records[] = new TokenTableRecord( $row );
		}

		return $records;
	}

	/**
	 * トークン情報を保存します。
	 */
	public function save( Token $token ): void {

		// データが存在する時はレコードの更新を行うが、symbol, decimalsの値は変更しない
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`chain_id`, `address`, `symbol`, `decimals`, `is_payable`)
			VALUES (%d, %s, %s, %d, %d)
			ON DUPLICATE KEY UPDATE
				`is_payable` = %d
		SQL;

		$sql = $this->wpdb()->prepare(
			$sql,
			$token->chainID(),
			$token->address()->value(),
			$token->symbol(),
			$token->decimals(),
			$token->isPayable(),
			$token->isPayable(),
		);

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[7217F4B3] Failed to add token data.' );
		}
	}
}
