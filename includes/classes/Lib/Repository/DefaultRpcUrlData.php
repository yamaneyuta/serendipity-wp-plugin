<?php

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use LogicException;

class DefaultRpcUrlData {

	private const CHAIN_ID_INDEX = 0;
	private const URL_INDEX      = 1;

	public function __construct() {
		$privatenet_l1         = ChainID::PRIVATENET_L1;
		$privatenet_l2         = ChainID::PRIVATENET_L2;
		$privatenet_l1_rpc_url = $this->getPrivatenetRpcURL( $privatenet_l1 );
		$privatenet_l2_rpc_url = $this->getPrivatenetRpcURL( $privatenet_l2 );

		// デフォルトのRPCデータ
		// ※ プライベートネットのURLはcompose.ymlに記載のhostname
		$default_rpc_data_json = <<<JSON
			{
				"data": [
					[ {$privatenet_l1}, "$privatenet_l1_rpc_url" ],
					[ {$privatenet_l2}, "$privatenet_l2_rpc_url" ]
				]
			}
		JSON;

		$this->default_rpc_data = json_decode( $default_rpc_data_json, true )['data'];
	}

	private array $default_rpc_data;

	/**
	 * プライベートネットのRPC URLを取得します。
	 *
	 * @param int $chain_ID チェーンID
	 * @return string RPC URL
	 * @throws LogicException プライベートネットで使用されるチェーンID以外が指定された場合
	 */
	private function getPrivatenetRpcURL( int $chain_ID ): string {
		$is_testing = DB_NAME === 'tests-wordpress';    // テストモード(phpunit)で実行中かどうかを判定

		switch ( $chain_ID ) {
			case ChainID::PRIVATENET_L1:
				return $is_testing ? 'http://tests-privatenet-1.local' : 'http://privatenet-1.local';
			case ChainID::PRIVATENET_L2:
				return $is_testing ? 'http://tests-privatenet-2.local' : 'http://privatenet-2.local';
			default:
				throw new \LogicException( '[69D0D666] Invalid chain ID. (' . $chain_ID . ')' );
		}
	}

	/**
	 * 指定したチェーンIDに対応するデフォルトのRPC URLを取得します。
	 *
	 * @param int $chain_ID チェーンID
	 * @return null|string RPC URL(デフォルトが定義されていない場合はnull)
	 */
	public function get( int $chain_ID ): ?string {
		foreach ( $this->default_rpc_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID ) {
				return $data[ self::URL_INDEX ];
			}
		}

		return null;
	}

	/**
	 * プライベートネットのL1に相当するRPC URLを取得します。
	 *
	 * @return string RPC URL
	 */
	public function getPrivatenetL1(): string {
		return $this->get( ChainID::PRIVATENET_L1 );
	}

	/**
	 * プライベートネットのL2に相当するRPC URLを取得します。
	 *
	 * @return string RPC URL
	 */
	public function getPrivatenetL2(): string {
		return $this->get( ChainID::PRIVATENET_L2 );
	}
}
