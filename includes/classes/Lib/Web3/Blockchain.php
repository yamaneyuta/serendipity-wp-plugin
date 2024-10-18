<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use phpseclib\Math\BigInteger;
use ReflectionClass;
use Web3\Eth;
use Web3\Formatters\BigNumberFormatter;
use Web3\Methods\EthMethod;

class Blockchain {
	public function __construct( string $rpc_url, ?float $timeout = 2 ) {
		$this->rpc_url = $rpc_url;
		$this->timeout = $timeout;
	}
	private string $rpc_url;
	private float $timeout;

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
			assert( ! in_array( 'eth_chainId', $allowedMethods ), '[36C3ECD5] `eth_chainId` method is already allowed.' );
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
		$eth->chainId(
			function ( $err, BigInteger $res ) use ( &$chain_ID_hex ) {
				if ( $err ) {
					throw $err;
				}
				$chain_ID_hex = Hex::from( $res );
			}
		);
		assert( ! is_null( $chain_ID_hex ), '[1BAA2783] Failed to get chain ID.' );
		Judge::checkAmountHex( $chain_ID_hex );

		return $chain_ID_hex;
	}

	/**
	 * ブロック番号を取得します。
	 */
	public function getBlockNumberHex(): string {
		/** @var string|null */
		$block_number_hex = null;
		$this->eth()->blockNumber(
			function ( $err, BigInteger $res ) use ( &$block_number_hex ) {
				if ( $err ) {
					throw $err;
				}
				$block_number_hex = Hex::from( $res );
			}
		);
		assert( ! is_null( $block_number_hex ), '[C38AC4D1] Failed to get block number.' );
		Judge::checkAmountHex( $block_number_hex );

		return $block_number_hex;
	}

	/**
	 * アカウントの残高を取得します。
	 */
	public function getBalanceHex( string $address ): string {
		Judge::checkAddress( $address );

		/** @var string|null */
		$balance_hex = null;
		$this->eth()->getBalance(
			$address,
			function ( $err, BigInteger $res ) use ( &$balance_hex ) {
				if ( $err ) {
					throw $err;
				}
				$balance_hex = Hex::from( $res );
			}
		);
		assert( ! is_null( $balance_hex ), '[72C38938] Failed to get balance.' );
		Judge::checkAmountHex( $balance_hex );

		return $balance_hex;
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
