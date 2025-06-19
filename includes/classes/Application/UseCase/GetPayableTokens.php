<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\TokenDto;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Lib\Logger\Logger;

/** 指定された投稿で支払い可能なトークン一覧を取得します */
class GetPayableTokens {
	public function __construct( PostRepository $post_repository, ChainRepository $chain_repository, TokenRepository $token_repository ) {
		$this->post_repository  = $post_repository;
		$this->chain_repository = $chain_repository;
		$this->token_repository = $token_repository;
	}

	private PostRepository $post_repository;
	private ChainRepository $chain_repository;
	private TokenRepository $token_repository;

	/** @return TokenDto[] */
	public function handle( int $post_id ): array {
		// 投稿の販売ネットワークを取得
		$post                     = $this->post_repository->get( $post_id );
		$selling_network_category = $post->sellingNetworkCategoryID();
		if ( is_null( $selling_network_category ) ) {
			Logger::warn( '[666BFD5D] Selling network category is null for post ID: ' . $post_id );
			return array();  // 販売ネットワークカテゴリが設定されていない場合は空の配列を返す
		}

		// 投稿に設定されている販売ネットワークカテゴリかつ接続可能なチェーン一覧を取得
		$payable_chains = ( new ChainsFilter() )
			->byNetworkCategoryID( $selling_network_category )
			->byConnectable()
			->apply( $this->chain_repository->all() );

		/** @var TokenDto[] */
		$result = array();

		// 各チェーンに対して支払い可能なトークンを取得
		$all_tokens = $this->token_repository->all();
		foreach ( $payable_chains as $chain ) {
			$payable_tokens = ( new TokensFilter() )
				->byChainID( $chain->id() )
				->byIsPayable( true )
				->apply( $all_tokens );

			foreach ( $payable_tokens as $token ) {
				$result[] = TokenDto::fromEntity( $token );
			}
		}

		return $result;
	}
}
