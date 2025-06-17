<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\GetBlockResult;

interface BlockchainClientService {
	/**
	 * 対象のチェーンでブロック番号またはタグを指定してブロック情報を取得します。
	 *
	 * @param BlockNumber|BlockTag $block_number_or_tag
	 */
	public function getBlockByNumber( $block_number_or_tag ): GetBlockResult;
}
