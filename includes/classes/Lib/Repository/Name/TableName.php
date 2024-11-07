<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Name;

use Cornix\Serendipity\Core\Lib\Repository\Name\Prefix;

class TableName {

	// 定数の値(テーブル名)は変更しないでください
	// テーブル作成済みの実環境と不整合が発生し、テストは通るが実環境でエラーが発生する、という状況になります。

	/**
	 * 指定されたテーブル名に接頭辞をつけて返します
	 * 作成するテーブル名はこのメソッドを使用してください
	 */
	private function addPrefix( string $table_name ): string {
		return ( new Prefix() )->tableName() . $table_name;
	}

	public function invoice(): string {
		return $this->addPrefix( 'invoice' );
	}

	public function invoiceNonce(): string {
		return $this->addPrefix( 'invoice_nonce' );
	}
}
