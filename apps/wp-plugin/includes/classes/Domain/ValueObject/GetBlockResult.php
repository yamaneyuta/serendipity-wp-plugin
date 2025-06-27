<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use stdClass;

class GetBlockResult {

	public function __construct( stdClass $get_block_by_number_response ) {
		$this->response = $get_block_by_number_response;
	}

	private stdClass $response;

	public function blockNumber(): BlockNumber {
		return BlockNumber::from( $this->response->number );
	}

	public function timestamp(): UnixTimestamp {
		// タイムスタンプはUNIX時間
		return new UnixTimestamp( hexdec( $this->response->timestamp ) );
	}
}
