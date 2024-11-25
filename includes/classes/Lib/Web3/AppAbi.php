<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use kornrunner\Keccak;

class AppAbi {

	/** @var array|null */
	private $abi_cache = null;

	/**
	 * 計算済みのtopicハッシュを保持
	 *
	 * @var array
	 */
	private $topicHashCache = array();

	public function get(): array {
		if ( is_null( $this->abi_cache ) ) {
			$abi_json        = <<<JSON
			{
				"abi": [
					{
						"anonymous": false,
						"inputs": [
							{
								"indexed": true,
								"internalType": "address",
								"name": "from",
								"type": "address"
							},
							{
								"indexed": true,
								"internalType": "address",
								"name": "to",
								"type": "address"
							},
							{
								"indexed": true,
								"internalType": "address",
								"name": "token",
								"type": "address"
							},
							{
								"indexed": false,
								"internalType": "uint256",
								"name": "amount",
								"type": "uint256"
							}
						],
						"name": "EffectivelyTransfer",
						"type": "event"
					},
					{
						"anonymous": false,
						"inputs": [
							{
								"indexed": true,
								"internalType": "address",
								"name": "account",
								"type": "address"
							},
							{
								"indexed": true,
								"internalType": "address",
								"name": "signer",
								"type": "address"
							},
							{
								"indexed": false,
								"internalType": "uint64",
								"name": "postID",
								"type": "uint64"
							},
							{
								"indexed": false,
								"internalType": "address",
								"name": "consumer",
								"type": "address"
							}
						],
						"name": "UnlockPaywall",
						"type": "event"
					},
					{
						"inputs": [
							{
								"internalType": "address",
								"name": "signer",
								"type": "address"
							},
							{
								"internalType": "uint64",
								"name": "postID",
								"type": "uint64"
							},
							{
								"internalType": "address",
								"name": "consumer",
								"type": "address"
							}
						],
						"name": "getPaywallStatus",
						"outputs": [
							{
								"internalType": "bool",
								"name": "isUnlocked",
								"type": "bool"
							},
							{
								"internalType": "uint128",
								"name": "invoiceID",
								"type": "uint128"
							},
							{
								"internalType": "uint256",
								"name": "unlockedBlockNumber",
								"type": "uint256"
							}
						],
						"stateMutability": "view",
						"type": "function"
					}
				]
			}
			JSON;
			$this->abi_cache = json_decode( $abi_json, true )['abi'];
		}

		return $this->abi_cache;
	}


	/**
	 * 指定したメソッドまたはイベントのtopic(フィルタ用のハッシュ値)を取得します。
	 *
	 * keccak256("UnlockPaywall(address,address,uint64,address)") のようなハッシュ値
	 */
	public function topicHash( string $func_or_event_name ): string {
		if ( ! array_key_exists( $func_or_event_name, $this->topicHashCache ) ) {
			$abi    = $this->get();
			$target = array_filter( $abi, fn( $item ) => $item['name'] === $func_or_event_name );
			assert( count( $target ) === 1 );
			$target = array_values( $target )[0];

			$input_types = array_map( fn( $item ) => $item['type'], $target['inputs'] );

			$hash = '0x' . Keccak::hash( $func_or_event_name . '(' . implode( ',', $input_types ) . ')', 256 );

			$this->topicHashCache[ $func_or_event_name ] = $hash;
		}

		return $this->topicHashCache[ $func_or_event_name ];
	}
}
