<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Constants\Config;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;
use phpseclib\Math\BigInteger;
use ReflectionClass;
use Web3\Eth;
use Web3\Formatters\BigNumberFormatter;
use Web3\Methods\EthMethod;

// memo.
// Ethを継承してリトライを行うクラスを作成する方法は、名前空間やクラス名がEthクラス内部で使用されておりややこしくなるため不採用
// ここでは各メソッドでリトライオブジェクトを使用するように実装している

class BlockchainClient {
	public function __construct( string $rpc_url ) {
		$this->rpc_url = $rpc_url;
		$this->timeout = Config::BLOCKCHAIN_REQUEST_TIMEOUT;
		$this->retryer = new BlockchainRetryer();
	}
	private string $rpc_url;
	private float $timeout;
	private BlockchainRetryer $retryer;

	private function eth(): Eth {
		return new Eth( $this->rpc_url, $this->timeout );
	}

	/**
	 * チェーンIDを取得します。
	 */
	public function getChainIDHex(): string {
		$eth = $this->eth();

		// Ethオブジェクトの内容を操作することで`eth_chainId`メソッドの追加を行う
		{
			$reflectionClass = new ReflectionClass( get_class( $eth ) );
			$property        = $reflectionClass->getProperty( 'allowedMethods' );
			$property->setAccessible( true );
			/** @var string[] */
			$allowedMethods = $property->getValue( $eth );
			assert( ! in_array( 'eth_chainId', $allowedMethods, true ), '[36C3ECD5] `eth_chainId` method is already allowed.' );
			$allowedMethods[] = 'eth_chainId';
			$property->setValue( $eth, $allowedMethods ); // 許可するメソッド一覧に`eth_chainId`を追加

			$methods_property = $reflectionClass->getProperty( 'methods' );
			$methods_property->setAccessible( true );
			$methods                = $methods_property->getValue( $eth );
			$methods['eth_chainId'] = new ChainIdMethod( 'eth_chainId', array() );  // `eth_chainId`メソッド呼び出し時に使うクラスを設定
			$methods_property->setValue( $eth, $methods );
		}

		/** @var string|null */
		$chain_ID_hex = null;
		$this->retryer->execute(
			function () use ( $eth, &$chain_ID_hex ) {
				$eth->chainId(
					function ( $err, BigInteger $res ) use ( &$chain_ID_hex ) {
						if ( $err ) {
							throw $err;
						}
						$chain_ID_hex = Hex::from( $res );
					}
				);
			}
		);
		assert( ! is_null( $chain_ID_hex ), '[1BAA2783] Failed to get chain ID.' );
		Validate::checkAmountHex( $chain_ID_hex );

		return $chain_ID_hex;
	}

	/**
	 * ブロック番号を取得します。
	 */
	public function getBlockNumber( string $tag = 'latest' ): BlockNumber {
		Validate::checkBlockTagName( $tag );

		/** @var string|null */
		$block_number_hex = null;
		if ( $tag === 'latest' ) {
			$block_number_hex = $this->getLatestBlockNumberHex();
		} else {
			$block_number_hex = $this->getBlockNumberByTag( $tag );
		}

		return BlockNumber::from( $block_number_hex );
	}

	/**
	 * 最新のブロック番号(HEX)を取得します。
	 */
	private function getLatestBlockNumberHex(): string {
		/** @var string|null */
		$block_number_hex = null;
		$this->retryer->execute(
			function () use ( &$block_number_hex ) {
				$this->eth()->blockNumber(
					function ( $err, BigInteger $res ) use ( &$block_number_hex ) {
						if ( $err ) {
							throw $err;
						}
						$block_number_hex = Hex::from( $res );
					}
				);
			}
		);
		assert( ! is_null( $block_number_hex ), '[C38AC4D1] Failed to get block number.' );
		Validate::checkAmountHex( $block_number_hex );

		return $block_number_hex;
	}

	/**
	 * eth_getBlockByNumberの呼び出しを行い、そのブロック番号をHEXで返します
	 */
	private function getBlockNumberByTag( string $tag ): string {
		// @see https://docs.chainstack.com/reference/ethereum-getblockbynumber#parameters

		/** @var string|null */
		$block_number_hex = null;
		$this->retryer->execute(
			function () use ( $tag, &$block_number_hex ) {
				$this->eth()->getBlockByNumber(
					$tag,
					false,  // false: トランザクションの詳細を取得しない
					function ( $err, $res ) use ( &$block_number_hex ) {
						if ( $err ) {
							throw $err;
						}

						$block_number_hex = $res->number; // $res->numberは16進数の文字列
					}
				);
			}
		);

		assert( ! is_null( $block_number_hex ), '[D3B8A88E] Failed to get block number.' );
		Validate::checkHex( $block_number_hex );

		return $block_number_hex;
	}


	/**
	 * アカウントの残高を取得します。
	 */
	public function getBalanceHex( string $address ): string {
		Validate::checkAddress( $address );

		/** @var string|null */
		$balance_hex = null;
		$this->retryer->execute(
			function () use ( $address, &$balance_hex ) {
				$this->eth()->getBalance(
					$address,
					function ( $err, BigInteger $res ) use ( &$balance_hex ) {
						if ( $err ) {
							throw $err;
						}
						$balance_hex = Hex::from( $res );
					}
				);
			}
		);
		assert( ! is_null( $balance_hex ), '[72C38938] Failed to get balance.' );
		Validate::checkAmountHex( $balance_hex );

		return $balance_hex;
	}

	public function getLogs( ...$args ) {
		$this->retryer->execute(
			function () use ( $args ) {
				$this->eth()->getLogs( ...$args );
			}
		);
	}
}

/**
 * @internal
 */
class ChainIdMethod extends EthMethod {

	protected $validators = array();

	protected $inputFormatters = array();

	protected $outputFormatters = array(
		BigNumberFormatter::class,
	);

	protected $defaultValues = array();
}
