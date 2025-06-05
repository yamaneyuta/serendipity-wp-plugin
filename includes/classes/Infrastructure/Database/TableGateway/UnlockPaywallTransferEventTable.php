<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;

/**
 * ペイウォール解除イベントのログ
 */
class UnlockPaywallTransferEventTable extends TableBase {
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->unlockPaywallTransferEvent() );
	}

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		$charset    = $this->wpdb()->get_charset_collate();
		$index_name = "idx_{$this->tableName()}_E1160E22";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`     timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`     varchar(191)  NOT NULL,
				`log_index`      int           NOT NULL,
				`from_address`   varchar(191)  NOT NULL,
				`to_address`     varchar(191)  NOT NULL,
				`token_address`  varchar(191)  NOT NULL,
				`amount_hex`     varchar(191)  NOT NULL,
				`transfer_type`  int           NOT NULL,
				PRIMARY KEY (`invoice_id`, `log_index`),
				KEY `{$index_name}` (`created_at`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[90A49762] Failed to create unlock paywall transfer event table. ' . $this->mysqli()->error );
		}
	}

	public function save( InvoiceID $invoice_id, int $log_index, Address $from, Address $to, Address $token_address, string $amount_hex, int $transfer_type ): void {
		Validate::checkAmountHex( $amount_hex );

		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`invoice_id`, `log_index`, `from_address`, `to_address`, `token_address`, `amount_hex`, `transfer_type`)
			VALUES (%s, %d, %s, %s, %s, %s, %d)
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_id->ulid(), $log_index, $from->value(), $to->value(), $token_address->value(), $amount_hex, $transfer_type );

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \RuntimeException( '[86C68ECA] Failed to save unlock paywall transfer event. ' . $this->wpdb()->last_error );
		}
	}
}
