<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Definition\NativeTokenDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Types\TokenType;

/**
 * トークンの情報を記録するテーブル
 * ※ ユーザーが登録するERC20等のデータ。ネイティブトークンに関してはプラグインアップデート時に不具合が入りそうなので記録しない。(ネイティブトークンの定義はPHPファイルで行う)
 */
class TokenTable {

	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb       = $wpdb ?? $GLOBALS['wpdb'];
		$this->mysqli     = ( new MySQLiFactory() )->create( $this->wpdb );
		$this->table_name = ( new TableName() )->token();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`chain_id`       bigint(20)    unsigned  NOT NULL,
				`token_address`  varchar(191)            NOT NULL,
				`symbol`         varchar(191)            NOT NULL,
				`decimals`       int(11)                 NOT NULL,
				PRIMARY KEY (`chain_id`, `token_address`)
			) ${charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * ネイティブトークンを除くトークンデータ一覧を取得します。
	 * パラメータでチェーンID、アドレス、シンボルを指定することで絞り込みができます。
	 *
	 * @param int|null    $chain_ID チェーンID
	 * @param string|null $address トークンアドレス
	 * @param string|null $symbol トークンシンボル
	 * @return TokenType[]
	 */
	public function select( ?int $chain_ID = null, ?string $contract_address = null, ?string $symbol = null ): array {
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
		if ( ! is_null( $contract_address ) ) {
			Judge::checkAddress( $contract_address );
			$wheres[] = $this->wpdb->prepare( '`token_address` = %s', $contract_address );
		}
		if ( ! is_null( $symbol ) ) {
			Judge::checkSymbol( $symbol );
			$wheres[] = $this->wpdb->prepare( '`symbol` = %s', $symbol );
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

	/**
	 * テーブルにトークン(ネイティブトークンは除く)を追加します。
	 */
	public function insert( int $chain_ID, string $contract_address, string $symbol, int $decimals ): void {
		Judge::checkChainID( $chain_ID );
		Judge::checkAddress( $contract_address );
		Judge::checkSymbol( $symbol );
		Judge::checkDecimals( $decimals );
		if ( $contract_address === Ethers::zeroAddress() ) {
			// アドレスゼロはテーブルに保存しない(NativeTokenSymbolDefinitionで定義する)ため例外を投げる
			throw new \InvalidArgumentException( '[02B46A5C] Contract address is zero address.' );
		}
		if ( $symbol === ( new NativeTokenDefinition() )->getSymbol( $chain_ID ) ) {
			// ネイティブトークンと同じシンボルはテーブルに保存しないため例外を投げる
			throw new \InvalidArgumentException( '[3A0C783C] Symbol is same as native token. - ' . $symbol );
		}

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
	 * テーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}
}
