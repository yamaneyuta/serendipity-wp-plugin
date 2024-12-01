<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\TokenClientFactory;

class TokenData {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->token();
	}

	private \wpdb $wpdb;
	private string $table_name;

	public function add( int $chain_ID, string $contract_address ): void {
		Judge::checkChainID( $chain_ID );
		Judge::checkAddress( $contract_address );

		// コントラクトからsymbolとdecimalsを取得して保存する
		$token_client = ( new TokenClientFactory() )->create( $chain_ID, $contract_address );
		$symbol       = $token_client->symbol();
		$decimals     = $token_client->decimals();

		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`chain_id`, `token_address`, `symbol`, `decimals`)
			VALUES (%d, %s, %s, %d)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $chain_ID, $contract_address, $symbol, $decimals );

		$result = $this->wpdb->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[7217F4B3] Failed to add token data.' );
		}
	}
}
