<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Service\BlockchainClientService;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\GetBlockResult;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainRetryer;
use phpseclib\Math\BigInteger;
use ReflectionClass;
use Web3\Eth;
use Web3\Formatters\BigNumberFormatter;
use Web3\Methods\EthMethod;

class BlockchainClientServiceImpl implements BlockchainClientService {

	public function __construct( Chain $chain ) {
		$this->eth     = new Eth( $chain->rpcURL(), Config::BLOCKCHAIN_REQUEST_TIMEOUT );
		$this->retryer = new BlockchainRetryer();
	}

	private Eth $eth;
	private BlockchainRetryer $retryer;

	/** @inheritdoc */
	public function getChainID(): ChainID {

		// Ethオブジェクトの内容を操作することで`eth_chainId`メソッドの追加を行う
		if ( true ) {
			$reflectionClass = new ReflectionClass( get_class( $this->eth ) );
			$property        = $reflectionClass->getProperty( 'allowedMethods' );
			$property->setAccessible( true );
			/** @var string[] */
			$allowedMethods = $property->getValue( $this->eth );
			if ( ! in_array( 'eth_chainId', $allowedMethods, true ) ) {
				$allowedMethods[] = 'eth_chainId';
				$property->setValue( $this->eth, $allowedMethods ); // 許可するメソッド一覧に`eth_chainId`を追加

				$methods_property = $reflectionClass->getProperty( 'methods' );
				$methods_property->setAccessible( true );
				$methods = $methods_property->getValue( $this->eth );
				assert( ! isset( $methods['eth_chainId'] ), '[629768BF] `eth_chainId` method is already defined.' );
				$methods['eth_chainId'] = new ChainIdMethod( 'eth_chainId', array() );  // `eth_chainId`メソッド呼び出し時に使うクラスを設定
				$methods_property->setValue( $this->eth, $methods );
			}
		}

		/** @var null|ChainID */
		$result = null;
		$this->retryer->execute(
			function () use ( &$result ) {
				$this->eth->chainId(
					function ( $err, BigInteger $res ) use ( &$result ) {
						if ( $err ) {
							throw $err;
						}
						$result = new ChainID( hexdec( $res->toHex() ) );
					}
				);
			}
		);
		assert( null !== $result, '[F6805A68] Result should not be null after retry.' );

		return $result;
	}

	/** @inheritdoc */
	public function getBlockByNumber( $block_number_or_tag ): GetBlockResult {
		if ( $block_number_or_tag instanceof BlockNumber ) {
			$block_number = $block_number_or_tag->hex();
		} elseif ( $block_number_or_tag instanceof BlockTag ) {
			$block_number = $block_number_or_tag->value();
		} else {
			throw new \InvalidArgumentException( '[FDB7CEF6] Invalid argument type. Expected BlockNumber or BlockTag. - ' . var_export( $block_number_or_tag, true ) );
		}

		/** @var null|GetBlockResult */
		$result = null;
		$this->retryer->execute(
			function () use ( $block_number, &$result ) {
				$this->eth->getBlockByNumber(
					$block_number,
					false, // false: トランザクションの詳細を取得しない
					function ( $err, $res ) use ( &$result ) {
						if ( $err ) {
							throw $err;
						}
						$result = new GetBlockResult( $res );
					}
				);
			}
		);
		assert( null !== $result, '[F6805A68] Result should not be null after retry.' );

		return $result;
	}
}

/** @internal */
class ChainIdMethod extends EthMethod {
	protected $validators       = array();
	protected $inputFormatters  = array();
	protected $outputFormatters = array( BigNumberFormatter::class );
	protected $defaultValues    = array();
}
