<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Factory\ChainRepositoryFactory;
use Cornix\Serendipity\Core\Application\Factory\PostRepositoryFactory;
use Cornix\Serendipity\Core\Application\Factory\TokenRepositoryFactory;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Lib\Logger\Logger;
use wpdb;

class GetPayableTokens {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	/**
	 *
	 * @return Token[]
	 */
	public function handle( int $post_id ): array {
		$post_repository  = ( new PostRepositoryFactory( $this->wpdb ) )->create();
		$chain_repository = ( new ChainRepositoryFactory( $this->wpdb ) )->create();
		$token_repository = ( new TokenRepositoryFactory( $this->wpdb ) )->create();

		// 投稿の販売ネットワークを取得
		$post                     = $post_repository->get( $post_id );
		$selling_network_category = $post->sellingNetworkCategoryID();
		if ( is_null( $selling_network_category ) ) {
			Logger::warn( '[666BFD5D] Selling network category is null for post ID: ' . $post_id );
			return array();  // 販売ネットワークカテゴリが設定されていない場合は空の配列を返す
		}

		// 投稿に設定されている販売ネットワークカテゴリかつ接続可能なチェーン一覧を取得
		$payable_chains = ( new ChainsFilter() )
			->byNetworkCategoryID( $selling_network_category )
			->byConnectable()
			->apply( $chain_repository->getAllChains() );

		/** @var Token[] */
		$result = array();

		// 各チェーンに対して支払い可能なトークンを取得
		$all_tokens = $token_repository->all();
		foreach ( $payable_chains as $chain ) {
			$payable_tokens = ( new TokensFilter() )
				->byChainID( $chain->id() )
				->byIsPayable( true )
				->apply( $all_tokens );

			foreach ( $payable_tokens as $token ) {
				$result[] = $token;
			}
		}

		return $result;
	}
}
