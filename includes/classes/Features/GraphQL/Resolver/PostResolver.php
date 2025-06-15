<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\GetPayableTokens;

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
			return array_map(
				fn( $token ) => $root_value['token'](
					$root_value,
					array(
						'chainID' => $token->chainID()->value(),
						'address' => $token->address()->value(),
					)
				),
				( new GetPayableTokens( $GLOBALS['wpdb'] ) )->handle( $post_ID )
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
