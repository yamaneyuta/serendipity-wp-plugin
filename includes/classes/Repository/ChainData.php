<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Config\Config;
use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * チェーンの情報を取得するクラス
 */
class ChainData {
	public function __construct( int $chian_ID ) {
		$this->chain_ID = $chian_ID;
	}

	private int $chain_ID;

	public function networkCategory(): ?NetworkCategory {
		$network_category_id = Config::NETWORK_CATEGORIES[ $this->chain_ID ] ?? null;
		return is_null( $network_category_id ) ? null : NetworkCategory::from( $network_category_id );
	}
}
