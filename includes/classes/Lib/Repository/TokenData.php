<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
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
		if ( $contract_address === Ethers::zeroAddress() ) {
			// アドレスゼロはテーブルに保存しない(NativeTokenSymbolDefinitionで定義する)ため例外を投げる
			throw new \InvalidArgumentException( '[02B46A5C] Contract address is zero address.' );
		}

		// コントラクトからsymbolとdecimalsを取得して保存する
		$token_client = ( new TokenClientFactory() )->create( $chain_ID, $contract_address );
		$symbol       = $token_client->symbol();
		$decimals     = $token_client->decimals();
		Judge::checkSymbol( $symbol );
		Judge::checkDecimals( $decimals );

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

	/**
	 * 指定されたチェーンのトークンデータ一覧を取得します。
	 * チェーンIDが指定されない場合、全てのトークンデータを取得します。
	 *
	 * @param int|null $chain_ID チェーンID
	 * @return TokenDataRecord[]
	 */
	public function get( ?int $chain_ID = null ): array {
		$sql = <<<SQL
			SELECT `chain_id`, `token_address`, `symbol`, `decimals`
			FROM `{$this->table_name}`
		SQL;

		if ( ! is_null( $chain_ID ) ) {
			$sql .= $this->wpdb->prepare( ' WHERE `chain_id` = %d', $chain_ID );
		}

		$result = $this->wpdb->get_results( $sql );
		if ( false === $result ) {
			throw new \Exception( '[CA8FE52D] Failed to get token data.' );
		}

		$records = array();
		foreach ( $result as $row ) {
			$chain_ID      = (int) $row->chain_id;
			$token_address = (string) $row->token_address;
			$symbol        = (string) $row->symbol;
			$decimals      = (int) $row->decimals;

			assert( Judge::isChainID( $chain_ID ), '[C4D50120] Invalid chain ID. ' . $chain_ID );
			assert( Judge::isAddress( $token_address ), '[6535A6C3] Invalid contract address. ' . $token_address );
			assert( Judge::isSymbol( $symbol ), '[C08FC67D] Invalid symbol. ' . $symbol );
			assert( Judge::isDecimals( $decimals ), '[79794512] Invalid decimals. ' . $decimals );

			$records[] = new TokenDataRecord( $chain_ID, $token_address, $symbol, $decimals );
		}

		return $records;
	}
}

class TokenDataRecord {
	public function __construct( int $chain_ID, string $contract_address, string $symbol, int $decimals ) {
		Judge::checkChainID( $chain_ID );
		Judge::checkAddress( $contract_address );

		$this->chain_ID         = $chain_ID;
		$this->contract_address = $contract_address;
		$this->symbol           = $symbol;
		$this->decimals         = $decimals;
	}

	private int $chain_ID;
	private string $contract_address;
	private string $symbol;
	private int $decimals;

	public function chainID(): int {
		return $this->chain_ID;
	}

	public function contractAddress(): string {
		return $this->contract_address;
	}

	public function symbol(): string {
		return $this->symbol;
	}

	public function decimals(): int {
		return $this->decimals;
	}
}
