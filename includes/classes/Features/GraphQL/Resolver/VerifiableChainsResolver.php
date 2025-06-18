<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ChainService;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Lib\Logger\Logger;

class VerifiableChainsResolver extends ResolverBase {

	public function __construct(
		AppContractRepository $app_contract_repository,
		ChainService $chain_service,
		PostRepository $post_repository
	) {
		$this->app_contract_repository = $app_contract_repository;
		$this->chain_service           = $chain_service;
		$this->post_repository         = $post_repository;
	}

	private AppContractRepository $app_contract_repository;
	private ChainService $chain_service;
	private PostRepository $post_repository;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		$selling_network_category_id = $this->post_repository->get( $post_ID )->sellingNetworkCategoryID();
		if ( is_null( $selling_network_category_id ) ) {
			Logger::warn( '[B4FC6E2A] Selling network category is null for post ID: ' . $post_ID );
			return array();  // 販売ネットワークカテゴリが設定されていない場合は空の配列を返す
		}

		// 投稿の販売ネットワークカテゴリに属するチェーン一覧を取得
		$chains_filter = ( new ChainsFilter() )->byNetworkCategoryID( $selling_network_category_id );
		$chains        = $chains_filter->apply( $this->chain_service->getAllChains() );

		$result = array();
		foreach ( $chains as $chain ) {
			// アプリケーションコントラクトがデプロイされており、チェーンに接続可能な場合は、検証可能なチェーンとして返す
			$app_contract         = $this->app_contract_repository->get( $chain->id() );
			$app_contract_address = is_null( $app_contract ) ? null : $app_contract->address();
			if ( ! is_null( $app_contract_address ) && $chain->connectable() ) {
				$result[] = $root_value['chain']( $root_value, array( 'chainID' => $chain->id() ) );
			}
		}

		return $result;
	}
}
