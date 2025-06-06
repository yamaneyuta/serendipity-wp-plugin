<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ServerSignerTableRecord;

/**
 * 署名用ウォレットの情報を記録するテーブル
 *
 * - `address`はウォレットの秘密鍵から生成可能だが、以下の目的で保持
 *   - ウォレットを作成したときの検証用
 *   - アドレスだけ参照する際の計算量削減
 * - 暗号化して保存する場合は`encryption_key`と`encryption_iv`に値が入り、平文の場合は共にNULL
 * - 暗号化は、値コピーで簡単にウォレットにインポートできないようにしているだけ(同一レコードに鍵があるためセキュリティ的には平文保存と同じ)
 * - 将来的に暗号化の鍵の保管場所を変更する場合は`encryption_key_storage_type`のような列を追加するなどで対応
 */
class ServerSignerTable extends TableBase {

	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->serverSigner() );
	}

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb()->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`        timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`        timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`address`           varchar(191)   NOT NULL,
				`private_key_data`  varchar(191)   NOT NULL,
				`encryption_key`    varchar(191),
				`encryption_iv`     varchar(191),
				PRIMARY KEY (`address`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[731EE9CF] Failed to create server signer table. ' . $this->mysqli()->error );
		}
	}

	public function get(): ?ServerSignerTableRecord {
		$sql = <<<SQL
			SELECT `address`, `private_key_data`, `encryption_key`, `encryption_iv`
			FROM `{$this->tableName()}`
		SQL;

		$results = $this->wpdb()->get_results( $sql );
		if ( false === $results ) {
			throw new \RuntimeException( '[667ACE83] Failed to get server signer data.' );
		} elseif ( count( $results ) > 1 ) {
			// 2件以上データが存在することはない
			throw new \RuntimeException( '[81CCE569] More than one server signer data found.' );
		}

		// データが存在しない場合はnullを返す
		return count( $results ) === 0 ? null : new ServerSignerTableRecord( $results[0] );
	}

	/**
	 * 署名用ウォレットの秘密鍵を保存します。
	 *
	 * @disregard P1009 Undefined type
	 */
	public function save(
		Address $address,
		string $private_key_data,
		#[\SensitiveParameter]
		?string $encryption_key,
		?string $encryption_iv
	): void {
		$result = $this->wpdb()->insert(
			$this->tableName(),
			array(
				'address'          => $address->value(),
				'private_key_data' => $private_key_data,
				'encryption_key'   => $encryption_key,
				'encryption_iv'    => $encryption_iv,
			),
		);

		if ( false === $result ) {
			throw new \RuntimeException( '[9EA75BCD] Failed to save server signer data: ' . $this->wpdb()->last_error );
		}
	}
}
