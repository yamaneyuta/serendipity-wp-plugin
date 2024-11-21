<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Post\ContentFilter;
use Cornix\Serendipity\Core\Lib\Post\PostContent;

class RequestPaidContentByNonceResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];
		/** @var string */
		$invoice_ID = $args['invoiceID'];
		/** @var string */
		$nonce = $args['nonce'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );
		// TODO: nonceが有効な値であることをチェック

		// 投稿の有料部分を取得
		// HTMLコメントを含まない投稿本文を取得
		$content = ( new PostContent( $post_ID ) )->getCommentRemoved();

		// 有料部分のコンテンツを取得
		$paid_content = ( new ContentFilter( $content ) )->getPaid();

		return array(
			'content' => $paid_content,
		);
	}
}
