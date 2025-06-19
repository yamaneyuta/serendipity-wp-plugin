<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\GetPost;
use Cornix\Serendipity\Core\Application\UseCase\GetPostPayableTokens;

class PostResolver extends ResolverBase {

	public function __construct(
		GetPost $get_post,
		GetPostPayableTokens $get_post_payable_tokens
	) {
		$this->get_post                = $get_post;
		$this->get_post_payable_tokens = $get_post_payable_tokens;
	}

	private GetPost $get_post;
	private GetPostPayableTokens $get_post_payable_tokens;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$post = $this->get_post->handle( $args['postID'] );

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post->id() );

		$payable_tokens_callback = function () use ( $root_value, $post ) {
			$payable_tokens = $this->get_post_payable_tokens->handle( $post->id() );

			return array_map(
				fn( $token ) => $root_value['token'](
					$root_value,
					array(
						'chainID' => $token->chainId(),
						'address' => $token->address(),
					)
				),
				$payable_tokens
			);
		};

		return array(
			'id'             => $post->id(),
			'title'          => $post->title(),
			'sellingPrice'   => fn() => $root_value['sellingPrice']( $root_value, array( 'postID' => $post->id() ) ),
			'sellingContent' => fn() => $root_value['sellingContent']( $root_value, array( 'postID' => $post->id() ) ),
			'payableTokens'  => $payable_tokens_callback,
		);
	}
}
