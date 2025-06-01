<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Logger\Logger;
use Cornix\Serendipity\Core\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Service\ChainService;
use Cornix\Serendipity\Core\Service\ChainsService;
use Cornix\Serendipity\Core\Repository\PaidContentData;

class VerifiableChainsResolver extends ResolverBase {

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

		$selling_network_category = ( new PaidContentData( $post_ID ) )->sellingNetworkCategory();
		if ( is_null( $selling_network_category ) ) {
			Logger::warn( '[B4FC6E2A] Selling network category is null for post ID: ' . $post_ID );
			return array();  // 販売ネットワークカテゴリが設定されていない場合は空の配列を返す
		}

		// 投稿の販売ネットワークカテゴリに属する全てのチェーンIDを取得
		$chain_IDs = is_null( $selling_network_category ) ? array() : ( new ChainsService() )->chainIDs( $selling_network_category );

		$result = array();
		foreach ( $chain_IDs as $chain_ID ) {
			// アプリケーションコントラクトがデプロイされており、チェーンに接続可能な場合は、検証可能なチェーンとして返す
			$app_contract         = ( new AppContractRepository() )->get( $chain_ID );
			$app_contract_address = is_null( $app_contract ) ? null : $app_contract->address;
			if ( ! is_null( $app_contract_address ) && ( new ChainService( $chain_ID ) )->connectable() ) {
				$result[] = $root_value['chain']( $root_value, array( 'chainID' => $chain_ID ) );
			}
		}

		return $result;
	}
}
