<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Web3;

use kornrunner\Keccak;
use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\BNConvert;
use Cornix\Serendipity\Core\Utils\LocalPath;
use Cornix\Serendipity\Core\Web3\DataType\OracleLatestData;
use Cornix\Serendipity\Core\Web3\DataType\PurchaseEventLogData;
use Cornix\Serendipity\Core\Web3\IContract;
use Web3\Contracts\Ethabi;
use Web3\Contracts\Types\Address;
use Web3\Contracts\Types\Str;
use Web3\Contracts\Types\Uinteger;
use Web3\Eth;

class Contract implements IContract {
	public function __construct( $chain_id ) {
		$this->chain_id = $chain_id;
	}

	/** @var int */
	private $chain_id;

	public function getChainId(): int {
		return $this->chain_id;
	}

	private function getRpcUrl(): string {
		$rpc_url = Database::getRpcUrl( $this->chain_id );
		if ( null === $rpc_url ) {
			Logger::error( 'rpc_url is null. chain id: ' . $this->chain_id );
			throw new \Exception( '{7AAA432C-B119-4EC6-954F-14BD6AEC6D7E}' );
		}
		return $rpc_url;
	}

	private function getEth(): Eth {
		return new Eth( $this->getRpcUrl() );
	}

	private function getViewContract(): \Web3\Contract {
		$contractMeta = json_decode( file_get_contents( LocalPath::getViewContractMetaDataPath() ) );

		$contract = new \Web3\Contract( $this->getRpcUrl(), $contractMeta->abi );
		return $contract->at( $contractMeta->networks->{$this->chain_id}->address );
	}

	private function getMainContractAddress(): string {
		$contractMeta = json_decode( file_get_contents( LocalPath::getMainContractMetaDataPath() ) );
		return $contractMeta->networks->{$this->chain_id}->address;
	}

	/**
	 * `Purchase`イベントのログを取得します。
	 *
	 * @param string $from_block_hex
	 * @param string $to_block_hex
	 * @return PurchaseEventLogData[]
	 */
	public function getPurchaseEventLog( string $from_block_hex, string $to_block_hex ): array {
		$contract_address = $this->getMainContractAddress();
		$eth              = $this->getEth();
		$signers          = Database::getSignerAddresses();  // 過去に使用したことのある署名用アドレス一覧を取得
		for ( $i = 0; $i < count( $signers ); $i++ ) {
			// topicsに指定するために32バイトに固定長に変換
			$signers[ $i ] = BNConvert::toFixedLengthHex( $signers[ $i ], 32 );
		}

		/** @var PurchaseEventLogData[] */
		$result = array();
		$eth->getLogs(
			array(
				'fromBlock' => BNConvert::toSlimHex( $from_block_hex ),
				'toBlock'   => BNConvert::toSlimHex( $to_block_hex ),
				'address'   => $contract_address,
				'topics'    => array(
					'0x' . Keccak::hash( 'Purchase(address,uint256,address,address,string,uint256,uint256,uint256,address)', 256 ),
					$signers,
				),
			),
			function ( $err, $logs ) use ( &$result, $contract_address ) {
				if ( $err ) {
					Logger::error( $err );
					throw $err;
				}

				$eth_abi = new Ethabi(
					array(
						'address' => new Address(),
						'uint'    => new Uinteger(),
						'string'  => new Str(),
					)
				);

				foreach ( $logs as $log ) {
					if ( $log->removed ) {
						// TODO: ログ出力
						continue;
					}
					// ----- topicsから値を取得 -----
					$signer = BNConvert::toFixedLengthHex( $log->topics[1], 20 );

					// ----- dataをデコード -----
					$data = $log->data;
					/** @var array */
					$decoded               = $eth_abi->decodeParameters( array( 'uint256', 'address', 'address', 'string', 'uint256', 'uint256', 'uint256', 'address' ), $data );
					$decoded               = self::toNumberResponse( $decoded, array( 0, 4, 5, 6 ), 16 );
					$i                     = 0;
					$ticket_id_hex         = $decoded[ $i++ ];
					$from_hex              = $decoded[ $i++ ];
					$to_hex                = $decoded[ $i++ ];
					$symbol                = $decoded[ $i++ ];
					$profit_hex            = $decoded[ $i++ ];
					$commission_hex        = $decoded[ $i++ ];
					$affiliate_hex         = $decoded[ $i++ ];
					$affiliate_account_hex = $decoded[ $i++ ];

					assert( $i === count( $decoded ) );
					assert( strtolower( $log->address ) === strtolower( $contract_address ) );

					$result[] = new PurchaseEventLogData(
						$this->getChainId(),
						$log->logIndex,
						$log->transactionIndex,
						$log->transactionHash,
						$log->blockHash,
						$log->blockNumber,
						$signer,
						$ticket_id_hex,
						$from_hex,
						$to_hex,
						$symbol,
						$profit_hex,
						$commission_hex,
						$affiliate_hex,
						$affiliate_account_hex
					);
				}
			}
		);

		return $result;
	}

	public function getSellableSymbolsInfo(): array {

		$result = array();
		$this->getViewContract()->call(
			'getSellableSymbolsInfo',
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					Logger::error( $err );
					throw $err;
				}

				$result = $res;

				// chainIdはint型に変換
				$result = self::toNumberResponse( $result, array( 'chainId' ), 10 );
				// blockNumberは16進数に変換
				$result = self::toNumberResponse( $result, array( 'blockNumber' ), 16 );
			}
		);

		return $result;
	}


	/** @inheritdoc */
	public function getOracleLatestData( array $symbols ): array {

		$contract_result = array();
		$this->getViewContract()->call(
			'getOracleLatestData',
			$symbols,
			function ( $err, $res ) use ( &$contract_result ) {
				if ( $err ) {
					Logger::error( $err );
					throw $err;
				}

				$contract_result = $res;

				// int型に変換
				$contract_result = self::toNumberResponse( $contract_result, array( 'updatedAts', 'decimalsArray' ), 10 );
				// 16進数に変換
				$contract_result = self::toNumberResponse( $contract_result, array( 'roundIds', 'answers', 'versions' ), 16 );
			}
		);

		// symbol毎のデータに変換
		$result = array();

		foreach ( $contract_result['symbols'] as $i => $symbol ) {
			$result[] = new OracleLatestData(
				$symbol,
				$contract_result['addresses'][ $i ],
				$contract_result['roundIds'][ $i ],
				$contract_result['answers'][ $i ],
				$contract_result['updatedAts'][ $i ],
				$contract_result['decimalsArray'][ $i ],
				$contract_result['descriptions'][ $i ],
				$contract_result['versions'][ $i ],
			);
		}

		return $result;
	}

	// コントラクトからの応答に含まれるBigInteger型を10進数または16進数の文字列に変換します。
	private static function toNumberResponse( array $response, array $properties, int $base ) {
		if ( 10 !== $base && 16 !== $base ) {
			Logger::error( "base: $base" );
			throw new \Exception( '{057D6018-967C-4C36-91BD-B4655879D300}' );
		}

		$result = $response;
		foreach ( $properties as $property ) {
			if ( is_array( $response[ $property ] ) ) {
				$result[ $property ] = array();
				foreach ( $response[ $property ] as $value ) {
					$result[ $property ][] = $base === 10 ? (int) $value->toString() : BNConvert::toSolHex( '0x' . $value->toHex() );
				}
			} else {
				$result[ $property ] = $base === 10 ? (int) $response[ $property ]->toString() : BNConvert::toSolHex( '0x' . $response[ $property ]->toHex() );
			}
		}
		return $result;
	}


	/** @inheritdoc */
	public function getPayableSymbolsInfo(): array {
		$result = array();
		$this->getViewContract()->call(
			'getPayableSymbolsInfo',
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					Logger::error( $err );
					throw $err;
				}

				$result = $res;

				// int型に変換できるものを変換
				$result = self::toNumberResponse( $result, array( 'decimals', 'tokenTypes', 'chainId' ), 10 );

				// BigInteger型は16進数に変換
				$result = self::toNumberResponse( $result, array( 'blockNumber' ), 16 );
			}
		);

		return $result;
	}

	/**
	 * 購入時の情報を取得します。
	 *
	 * @param array  $signer_addresses
	 * @param int    $post_id
	 * @param string $account
	 * @return array{ signer:sting, isPurchased: bool, chainId: int, blockNumber: string }
	 */
	public function getPurchasedInfo( array $signer_addresses, int $post_id, string $account ): array {
		$purchased_info = array();
		$this->getViewContract()->call(
			'getPurchasedInfo',
			$signer_addresses,
			'0x' . dechex( $post_id ),
			$account,
			function ( $err, $res ) use ( &$purchased_info ) {
				if ( $err ) {
					Logger::error( $err );
					throw $err;
				}
				$purchased_info = $res;

				// int型に変換できるものを変換
				$purchased_info = self::toNumberResponse( $purchased_info, array( 'chainId' ), 10 );

				// BigInteger型は16進数に変換
				$purchased_info = self::toNumberResponse( $purchased_info, array( 'blockNumber' ), 16 );
			}
		);

		return $purchased_info;
	}
}
