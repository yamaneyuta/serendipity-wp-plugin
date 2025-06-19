<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ChainService;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;

class NetworkCategoryResolver extends ResolverBase {

	public function __construct( ChainService $chain_service ) {
		$this->chain_service = $chain_service;
	}
	private ChainService $chain_service;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$network_category_id = new NetworkCategoryID( $args['networkCategoryID'] );

		$sellable_symbols_callback = function () use ( $network_category_id ) {
			Validate::checkHasEditableRole();  // 投稿編集者権限以上が必要
			return ( new SellableSymbols() )->get( $network_category_id );
		};

		// ネットワークカテゴリで絞り込んだチェーン一覧を取得
		$chains_filter = ( new ChainsFilter() )->byNetworkCategoryID( $network_category_id );
		$chains        = $chains_filter->apply( $this->chain_service->getAllChains() );

		$chains_callback = function () use ( $root_value, $chains ) {
			return array_map(
				function ( $chain ) use ( $root_value ) {
					return $root_value['chain']( $root_value, array( 'chainID' => $chain->id() ) );
				},
				$chains
			);
		};

		return array(
			'id'              => $network_category_id->value(),
			'chains'          => $chains_callback,
			'sellableSymbols' => $sellable_symbols_callback,
		);
	}
}
