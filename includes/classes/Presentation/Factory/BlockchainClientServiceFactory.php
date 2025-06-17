<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\Factory;

use Cornix\Serendipity\Core\Domain\Service\BlockchainClientService;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\BlockchainClientServiceImpl;

class BlockchainClientServiceFactory {

	public function create( ChainID $chain_id ): BlockchainClientService {
		$chain_service = ( new ChainServiceFactory() )->create();
		$chain         = $chain_service->getChain( $chain_id );

		return new BlockchainClientServiceImpl( $chain );
	}
}
