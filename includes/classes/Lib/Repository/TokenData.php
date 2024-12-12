<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\TokenClientFactory;
use Cornix\Serendipity\Core\Types\TokenType;

class TokenData {
	public function __construct( ?\wpdb $wpdb = null ) {
		$this->wpdb       = $wpdb ?? $GLOBALS['wpdb'];
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

		// TODO: アドレスのバイトコードを取得し、存在しない場合はエラーとする処理をここに追加

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
	 * @param int|null    $chain_ID チェーンID
	 * @param string|null $address トークンアドレス
	 * @return TokenType[]
	 */
	public function get( ?int $chain_ID = null, ?string $address = null ): array {
		$sql = <<<SQL
			SELECT `chain_id`, `token_address`, `symbol`, `decimals`
			FROM `{$this->table_name}`
		SQL;

		// 条件がある場合はWHERE句を追加
		$wheres = array();
		if ( ! is_null( $chain_ID ) ) {
			Judge::checkChainID( $chain_ID );
			$wheres[] = $this->wpdb->prepare( '`chain_id` = %d', $chain_ID );
		}
		if ( ! is_null( $address ) ) {
			Judge::checkAddress( $address );
			assert( $address !== Ethers::zeroAddress(), '[D2733384] Contract address is zero address.' );
			$wheres[] = $this->wpdb->prepare( '`token_address` = %s', $address );
		}
		if ( ! empty( $wheres ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $wheres );
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

			$records[] = TokenType::from( $chain_ID, $token_address, $symbol, $decimals );
		}

		return $records;
	}
}
