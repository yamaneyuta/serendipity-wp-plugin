<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Infrastructure\Retry\Retryer;
use Cornix\Serendipity\Core\Constant\Config;

class BlockchainRetryer {
	public function __construct() {
		$this->intervals_ms = Config::BLOCKCHAIN_REQUEST_RETRY_INTERVALS_MS;
	}
	/** @var int[] */
	private array $intervals_ms;

	public function execute( callable $callback ) {
		return ( new Retryer() )->execute( $callback, $this->intervals_ms );
	}
}
