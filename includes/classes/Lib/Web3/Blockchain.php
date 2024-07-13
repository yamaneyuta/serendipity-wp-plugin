<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use phpseclib\Math\BigInteger;
use ReflectionClass;
use Web3\Eth;
use Web3\Formatters\BigNumberFormatter;
use Web3\Methods\EthMethod;

class Blockchain {
	public function __construct( string $rpc_url ) {
		$this->rpc_url = $rpc_url;
	}
	private string $rpc_url;

	/**
	 * チェーンIDを取得します。
	 */
	public function getChainIDHex(): string {
		$eth = new Eth( $this->rpc_url );

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
			$methods['eth_chainId'] = new ChainId( 'eth_chainId', array() );  // `eth_chainId`メソッド呼び出し時に使うクラスを設定
			$methods_property->setValue( $eth, $methods );
		}

		$chain_ID_hex = '0x00';
		$eth->chainId(
			function ( $err, BigInteger $res ) use ( &$chain_ID_hex ) {
				if ( $err ) {
					throw $err;
				}
				$chain_ID_hex = '0x' . $res->toHex();
			}
		);
		assert( $chain_ID_hex !== '0x00', '[1BAA2783] Failed to get chain ID.' );

		return $chain_ID_hex;
	}

	/**
	 * ブロック番号を取得します。
	 */
	public function getBlockNumberHex(): string {
		$eth = new Eth( $this->rpc_url );

		$block_number_hex = '0x00';
		$eth->blockNumber(
			function ( $err, BigInteger $res ) use ( &$block_number_hex ) {
				if ( $err ) {
					throw $err;
				}
				$block_number_hex = '0x' . $res->toHex();
			}
		);
		assert( $block_number_hex !== '0x00', '[C38AC4D1] Failed to get block number.' );

		return $block_number_hex;
	}

	/**
	 * アカウントの残高を取得します。
	 */
	public function getBalanceHex( string $address ): string {
		$eth = new Eth( $this->rpc_url );

		$balance_hex = '0x00';
		$eth->getBalance(
			$address,
			function ( $err, BigInteger $res ) use ( &$balance_hex ) {
				if ( $err ) {
					throw $err;
				}
				$balance_hex = '0x' . $res->toHex();
			}
		);

		return $balance_hex;
	}
}

/**
 * @internal
 */
class ChainId extends EthMethod {

	protected $validators = array();

	protected $inputFormatters = array();

	protected $outputFormatters = array(
		BigNumberFormatter::class,
	);

	protected $defaultValues = array();
}
