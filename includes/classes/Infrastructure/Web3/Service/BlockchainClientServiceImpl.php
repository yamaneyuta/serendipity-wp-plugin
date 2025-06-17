<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Service\BlockchainClientService;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\GetBlockResult;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainRetryer;
use Web3\Eth;

class BlockchainClientServiceImpl implements BlockchainClientService {

	public function __construct( Chain $chain ) {
		$this->eth     = new Eth( $chain->rpcURL(), Config::BLOCKCHAIN_REQUEST_TIMEOUT );
		$this->retryer = new BlockchainRetryer();
	}

	private Eth $eth;
	private BlockchainRetryer $retryer;

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
