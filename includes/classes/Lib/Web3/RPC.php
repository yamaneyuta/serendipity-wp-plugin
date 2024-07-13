<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use phpseclib\Math\BigInteger;
use ReflectionClass;
use Web3\Eth;
use Web3\Formatters\BigNumberFormatter;
use Web3\Methods\EthMethod;

class RPC {
	public function __construct( string $rpc_url ) {
		$this->rpc_url = $rpc_url;
	}
	private string $rpc_url;

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
