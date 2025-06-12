<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Name;

use Cornix\Serendipity\Core\Repository\Name\Prefix;

class TableName {

	// 定数の値(テーブル名)は変更しないでください
	// テーブル作成済みの実環境と不整合が発生し、テストは通るが実環境でエラーが発生する、という状況になります。

	/**
	 * 指定されたテーブル名に接頭辞をつけて返します
	 * 作成するテーブル名はこのメソッドを使用してください
	 */
	private function addPrefix( string $table_name ): string {
		return ( new Prefix() )->tableNamePrefix() . $table_name;
	}

	/** 発行した請求書情報を記録するテーブル名 */
	public function invoice(): string {
		return $this->addPrefix( 'invoice' );
	}

	/** ペイウォール解除時のトランザクションに関するデータを記録するテーブル名 */
	public function unlockPaywallTransaction(): string {
		return $this->addPrefix( 'unlock_paywall_transaction' );
	}

	/** ペイウォール解除時のトークン転送イベントの内容を記録するテーブル名 */
	public function unlockPaywallTransferEvent(): string {
		return $this->addPrefix( 'unlock_paywall_transfer_event' );
	}

	/** Appコントラクトの情報を記録するテーブル名 */
	public function appContract(): string {
		return $this->addPrefix( 'app_contract' );
	}

	/** (支払時に使用する)トークンの情報を記録するテーブル名 */
	public function chain(): string {
		return $this->addPrefix( 'chain' );
	}

	/** (支払時に使用する)トークンの情報を記録するテーブル名 */
	public function token(): string {
		return $this->addPrefix( 'token' );
	}

	/** Oracleの定義を記録するテーブル名 */
	public function oracle(): string {
		return $this->addPrefix( 'oracle' );
	}

	public function paidContent(): string {
		return $this->addPrefix( 'paid_content' );
	}

	public function serverSigner(): string {
		return $this->addPrefix( 'server_signer' );
	}
}
