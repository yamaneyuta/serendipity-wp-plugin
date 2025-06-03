<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\TableGateway;

interface ITable {
	/**
	 * テーブルを作成します。
	 */
	public function create(): void;

	/**
	 * テーブルを削除します。
	 */
	public function drop(): void;
}
