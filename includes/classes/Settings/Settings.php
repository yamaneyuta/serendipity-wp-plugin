<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Settings;

use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\TypeValidator;
use Cornix\Serendipity\Core\Web3\ChainId;

/**
 * 設定を取得または設定するクラス。
 * Databaseクラスから取得した値を加工する操作はこのクラスで行います。
 * Databaseクラスを通しての設定の保存時、整合性チェックはDatabaseクラスで行うため、このクラスでのチェックは不要。
 *
 * @package Cornix\Serendipity\Core\Settings
 */
class Settings {

	/**
	 * 現在動作中のネットワーク種別でRPC URLが登録済みのチェーンID一覧を取得します。
	 *
	 * @return int[] チェーンID一覧
	 */
	public static function getActiveNetworksRegisteredChainIds(): array {
		/** @var int[] */
		$result = array();

		// 現在動作中のネットワーク種別を取得
		$active_network_type = Database::getActiveNetworkType();
		// 現在動作中のネットワークが登録されていない場合は空配列を返す
		if ( $active_network_type === null ) {
			Logger::warn( '[95D570A7] Network type is not set.' );
			return array();
		}
		// 現在動作中のネットワーク種別に対応するチェーンID一覧を取得
		$chain_ids = Constants::get( "networks.{$active_network_type}.chainIds" );

		// 登録済みRPC URL一覧を取得
		$rpc_urls = Database::getRpcUrls();

		foreach ( $rpc_urls as $chain_id => $rpc_url ) {
			// $chain_idが$chain_idsに含まれている場合は、$chain_idを結果に追加
			if ( in_array( $chain_id, $chain_ids, true ) ) {
				$result[] = (int) $chain_id;
			}
		}

		return $result;
	}

	/**
	 * RPC URL一覧を取得します。
	 * デプロイ済みのチェーンIDに対応するRPC URLが登録されていない場合はnullが設定されます。
	 *
	 * @return array<int, string>
	 * format: { 1: "https://xxxxx", 137: "https://xxxxx", 5777: null }
	 */
	public static function getAllRpcUrls(): array {
		$rpc_urls = Database::getRpcUrls();

		// デプロイ済みのチェーンIDに対応するRPC URLが登録されていない場合はnullを設定
		$deployed_chain_ids = ChainId::getAllDeployedChainIds();
		foreach ( $deployed_chain_ids as $chain_id ) {
			$rpc_urls[ $chain_id ] = $rpc_urls[ $chain_id ] ?? null;
		}

		return $rpc_urls;
	}

	public static function getRpcUrls( string $network_type ): array {
		$all_rpc_urls = self::getAllRpcUrls();

		$result = array();
		foreach ( $all_rpc_urls as $chain_id => $rpc_url ) {
			// ネットワーク種別が一致する場合のみ追加
			if ( ChainId::getNetworkType( $chain_id ) === $network_type ) {
				$result[ $chain_id ] = $rpc_url;
			}
		}
		return $result;
	}

	/**
	 * このプラグインの動作ネットワーク種別を取得します。
	 *
	 * @return null|string 'mainnet' | 'testnet' | 'privatenet' | null
	 */
	public static function getActiveNetworkType(): ?string {
		return Database::getActiveNetworkType();
	}

	/**
	 * 指定したネットワークにおける、ブロックが確定したとみなすための待機ブロック数一覧を取得します。
	 * 待機ブロック数が設定されていない場合はnullが設定されます。
	 *
	 * @return array<string, int|null>
	 * format: { "1": 12, "137": null }
	 */
	public static function getTxConfirmations( string $network_type ): array {
		if ( ! TypeValidator::isNetworkType( $network_type ) ) {
			Logger::error( '[C49815E8] Invalid network type. network_type: ' . $network_type );
			throw new \Exception( '{A9D327BE-8C63-495F-BDA2-1043FBE81C61}' );
		}

		// すべてのネットワーク種別に対応するチェーンID一覧を取得
		$tx_confirmations = Database::getTxConfirmations();

		// 指定したネットワーク種別のデプロイ済みチェーンID一覧を取得
		$deployed_chain_ids = ChainId::getDeployedChainIds( $network_type );

		$result = array();
		foreach ( $deployed_chain_ids as $chain_id ) {
			$result[ $chain_id ] = $tx_confirmations[ $chain_id ] ?? null;
		}

		return $result;
	}

	public static function getPayableSymbols( array $chain_ids ) {
		$all_payable_symbols = Database::getAllPayableSymbols();

		$result = array();
		foreach ( $chain_ids as $chain_id ) {
			// 設定が保存されていない場合は空配列を設定
			$result[ $chain_id ] = $all_payable_symbols[ $chain_id ] ?? array();
		}

		return $result;
	}

	public static function getAllPayableSymbols() {
		return Database::getAllPayableSymbols();
	}
}
