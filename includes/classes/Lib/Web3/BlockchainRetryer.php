<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Algorithm\Retryer;
use Cornix\Serendipity\Core\Config\Config;

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
