<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\ChainData;
use Cornix\Serendipity\Core\Lib\Repository\PayableChainIDs;

class ChainResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_id = $args['chainID'];

		$is_payable_callback = function () use ( $chain_id ) {
			// 購入可能なチェーンID一覧を取得
			$network_category  = ( new ChainData() )->getNetworkCategory( $chain_id );
			$payable_chain_ids = ( new PayableChainIDs() )->get( $network_category );
			return in_array( $chain_id, $payable_chain_ids );
		};

		return array(
			'id'        => $chain_id,
			'isPayable' => $is_payable_callback,
		);
	}
}
