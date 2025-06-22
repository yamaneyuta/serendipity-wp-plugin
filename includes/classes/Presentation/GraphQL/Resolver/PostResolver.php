<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetPost;
use Cornix\Serendipity\Core\Application\UseCase\GetPayableTokens;

class PostResolver extends ResolverBase {

	public function __construct(
		GetPost $get_post,
		GetPayableTokens $get_payable_tokens,
		UserAccessChecker $user_access_checker
	) {
		$this->get_post            = $get_post;
		$this->get_payable_tokens  = $get_payable_tokens;
		$this->user_access_checker = $user_access_checker;
	}

	private GetPost $get_post;
	private GetPayableTokens $get_payable_tokens;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$post = $this->get_post->handle( $args['postID'] );

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post->id() );

		$payable_tokens_callback = function () use ( $root_value, $post ) {
			$payable_tokens = $this->get_payable_tokens->handle( $post->id() );

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
