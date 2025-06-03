<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Repository\TableGateway\ChainTable;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;

/**
 * チェーンの情報を取得するクラス
 */
class ChainService {
	public function __construct( int $chian_ID, ?\wpdb $wpdb = null ) {
		$this->chain_ID    = $chian_ID;
		$this->chain_table = new ChainTable( $wpdb ?? $GLOBALS['wpdb'] );
	}

	private int $chain_ID;
	private ChainTable $chain_table;

	private function record() {
		$records = $this->chain_table->select( $this->chain_ID );
		assert( count( $records ) <= 1, '[3E69C835] ChainData::record() should return at most one record.' );
		return empty( $records ) ? null : $records[0];
	}

	/** RPC URLを取得します */
	public function rpcURL(): ?string {
		$record = $this->record();
		return is_null( $record ) ? null : $record->rpcURL();
	}

	/** RPC URLを設定します */
	public function setRpcURL( ?string $rpc_url ): void {
		$this->chain_table->updateRpcURL( $this->chain_ID, $rpc_url );
	}

	/** このチェーンに接続可能かどうかを取得します */
	public function connectable(): bool {
		$record = $this->record();
		// RPC URLが設定されていれば接続可能とする
		return ! is_null( $record ) && ! is_null( $record->rpcURL() ) && ! empty( $record->rpcURL() );
	}

	/** このチェーンのネットワークカテゴリを取得します */
	public function networkCategory(): NetworkCategory {
		$network_category = NetworkCategory::from( Config::NETWORK_CATEGORIES[ $this->chain_ID ] ?? null );
		if ( is_null( $network_category ) ) {
			throw new \UnexpectedValueException( '[B3FE6205] Network category ID is not defined for chain ID: ' . var_export( $this->chain_ID, true ) );
		}

		return $network_category;
	}

	/**
	 * このチェーンでの待機ブロック数を取得します
	 *
	 * @return null|int|string
	 */
	public function confirmations() {
		$record = $this->record();
		return is_null( $record ) ? null : $record->confirmations();
	}

	/**
	 * このチェーンの待機ブロック数を設定します
	 *
	 * @param int|string $confirmations
	 */
	public function setConfirmations( $confirmations ): void {
		$this->chain_table->updateConfirmations( $this->chain_ID, $confirmations );
	}
}
