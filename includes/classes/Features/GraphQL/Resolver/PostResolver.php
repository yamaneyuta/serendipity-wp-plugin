<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\GetPostPayableTokens;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;

class PostResolver extends ResolverBase {

	public function __construct(
		ChainRepository $chain_repository,
		PostRepository $post_repository,
		TokenRepository $token_repository
	) {
		$this->chain_repository = $chain_repository;
		$this->post_repository  = $post_repository;
		$this->token_repository = $token_repository;
	}

	private ChainRepository $chain_repository;
	private PostRepository $post_repository;
	private TokenRepository $token_repository;

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

		$payable_tokens_callback = function () use ( $root_value, $post_ID ) {
			$payable_tokens = ( new GetPostPayableTokens(
				$this->post_repository,
				$this->chain_repository,
				$this->token_repository
			) )->handle( $post_ID );

			return array_map(
				fn( $token ) => $root_value['token'](
					$root_value,
					array(
						'chainID' => $token->chainID()->value(),
						'address' => $token->address()->value(),
					)
				),
				$payable_tokens
			);
		};

		return array(
			'id'             => $post_ID,
			'title'          => fn() => get_the_title( $post_ID ),
			'sellingPrice'   => fn() => $root_value['sellingPrice']( $root_value, array( 'postID' => $post_ID ) ),
			'sellingContent' => fn() => $root_value['sellingContent']( $root_value, array( 'postID' => $post_ID ) ),
			'payableTokens'  => $payable_tokens_callback,
		);
	}
}
