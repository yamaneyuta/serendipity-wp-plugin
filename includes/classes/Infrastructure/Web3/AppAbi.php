<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use kornrunner\Keccak;
use stdClass;
use Web3\Contracts\Ethabi;

class AppAbi {

	/** @var array|null */
	private $abi_cache = null;

	/**
	 * 計算済みのtopicハッシュを保持
	 *
	 * @var array
	 */
	private $topic_hash_cache = array();

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
								"name": "signer",
								"type": "address"
							},
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
								"indexed": false,
								"internalType": "address",
								"name": "token",
								"type": "address"
							},
							{
								"indexed": false,
								"internalType": "uint256",
								"name": "amount",
								"type": "uint256"
							},
							{
								"indexed": false,
								"internalType": "uint128",
								"name": "invoiceID",
								"type": "uint128"
							},
							{
								"indexed": false,
								"internalType": "uint32",
								"name": "transferType",
								"type": "uint32"
							}
						],
						"name": "UnlockPaywallTransfer",
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
	 * eth_getLogsで取得したログオブジェクトのイベント名を取得します。
	 */
	private function getEventName( stdClass $log ): ?string {
		/** @var string */
		$log_topic_hash = $log->topics[0];

		// キャッシュに存在する場合はそのまま返す
		/** @var string|false */
		$result = array_search( $log_topic_hash, $this->topic_hash_cache, true );
		if ( is_string( $result ) ) {
			return $result;
		}

		// 見つからない場合は検索
		// すべてのイベント名を取得
		$event_names = array_map( fn( $item ) => $item['name'], $this->get() );
		foreach ( $event_names as $event_name ) {
			if ( $this->topicHash( $event_name ) === $log_topic_hash ) {
				return $event_name;
			}
		}

		assert( false, "[0FACDB6E] Unknown event name. log_topic_hash: {$log_topic_hash}, log: " . json_encode( $log ) );
		return null;
	}

	/**
	 * 指定したイベントのデコードされた引数を取得します。
	 *
	 * @return array<string,mixed>|null
	 */
	public function decodeEventParameters( stdClass $log ): ?array {
		$event_name = $this->getEventName( $log );
		if ( is_null( $event_name ) ) {
			assert( false, '[F88AADB9] Unknown event name.' );
			return null;
		}

		$abi     = $this->get();
		$eth_abi = new Ethabi( $abi );

		// indexedな引数はtopicsに格納されており、その他の引数はdataに格納されている
		// まずはイベントの引数をindexedありと無しとでそれぞれ取得
		$inputs         = array_values( array_filter( $abi, fn( $item ) => $item['name'] === $event_name ) )[0]['inputs'];
		$indexed_inputs = array_values( array_filter( $inputs, fn( $item ) => $item['indexed'] ) );
		$rest_inputs    = array_values( array_filter( $inputs, fn( $item ) => ! $item['indexed'] ) );

		$result = array();
		// indexedな引数をデコードして戻り値の配列へ追加
		if ( count( $indexed_inputs ) > 0 ) {
			foreach ( $indexed_inputs as $index => $input ) {
				$type                     = $input['type'];
				$data                     = $log->topics[ $index + 1 ]; // イベントの引数が記録されるのはインデックスが1から
				$decoded                  = $eth_abi->decodeParameter( $type, $data );
				$result[ $input['name'] ] = $this->correctDecodedValue( $type, $decoded );
			}
		}
		// dataに格納されている引数をデコードして戻り値の配列へ追加
		if ( count( $rest_inputs ) > 0 ) {
			$data = $log->data;
			/** @var array */
			$decoded_array = $eth_abi->decodeParameters( array_map( fn( $item ) => $item['type'], $rest_inputs ), $data );
			foreach ( $rest_inputs as $index => $input ) {
				$decoded                  = $decoded_array[ $index ];
				$result[ $input['name'] ] = $this->correctDecodedValue( $input['type'], $decoded );
			}
		}

		return $result;
	}

	/**
	 * web3-phpでデコードされた値を補正します。
	 */
	private function correctDecodedValue( string $type, $value ) {
		if ( $type === 'address' ) {
			assert( Ethers::isAddress( $value ), "[442797EA] Invalid address. value: {$value}" );
			return Ethers::getAddress( $value );
		}

		return $value;
	}

	/**
	 * 指定したメソッドまたはイベントのtopic(フィルタ用のハッシュ値)を取得します。
	 *
	 * keccak256("UnlockPaywall(address,address,uint64,uint128,address)") のようなハッシュ値
	 */
	public function topicHash( string $func_or_event_name ): string {
		if ( ! array_key_exists( $func_or_event_name, $this->topic_hash_cache ) ) {
			$abi    = $this->get();
			$target = array_filter( $abi, fn( $item ) => $item['name'] === $func_or_event_name );
			assert( count( $target ) === 1, '[841A8DFC] Invalid function or event name. ' . $func_or_event_name );
			$target = array_values( $target )[0];

			$input_types = array_map( fn( $item ) => $item['type'], $target['inputs'] );

			$hash = '0x' . Keccak::hash( $func_or_event_name . '(' . implode( ',', $input_types ) . ')', 256 );

			$this->topic_hash_cache[ $func_or_event_name ] = $hash;
		}

		return $this->topic_hash_cache[ $func_or_event_name ];
	}
}
