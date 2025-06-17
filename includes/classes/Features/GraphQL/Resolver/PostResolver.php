<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\GetPayableTokens;
use Cornix\Serendipity\Core\Infrastructure\Factory\ChainRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\PostRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\TokenRepositoryFactory;

class PostResolver extends ResolverBase {

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
			$post_repository  = ( new PostRepositoryFactory() )->create();
			$chain_repository = ( new ChainRepositoryFactory() )->create();
			$token_repository = ( new TokenRepositoryFactory() )->create();

			return array_map(
				fn( $token ) => $root_value['token'](
					$root_value,
					array(
						'chainID' => $token->chainID()->value(),
						'address' => $token->address()->value(),
					)
				),
				( new GetPayableTokens( $post_repository, $chain_repository, $token_repository ) )->handle( $post_ID )
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
