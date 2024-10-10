<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\ChainData;
use Cornix\Serendipity\Core\Lib\Repository\PurchasableChainIDs;

class ChainResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_id = $args['chainID'];

		$enabled_callback = function () use ( $chain_id ) {
			// 購入可能なチェーンID一覧を取得
			$all_chain_ids = ( new PurchasableChainIDs() )->get( ( new ChainData() )->getNetworkCategory( $chain_id ) );
			return in_array( $chain_id, $all_chain_ids );
		};

		return array(
			'id'      => $chain_id,
			'enabled' => $enabled_callback, // TODO: enabled -> payable
		);
	}
}
