<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Post\ContentAnalyzer;
use Cornix\Serendipity\Core\Lib\Post\ContentFilter;
use Cornix\Serendipity\Core\Lib\Post\PostContent;
use Cornix\Serendipity\Core\Types\SellingPostContentInfoType;

class SellingPostContentInfoResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return SellingPostContentInfoType|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿が公開済み、または編集可能な権限がある時に有料部分に指定したコンテンツの情報を取得します。
		if ( ! $this->isPublishedOrEditable( $post_ID ) ) {
			throw new \LogicException( '[9A22060C] You do not have permission to access this post.' );
		}

		// HTMLコメントを含まない投稿本文を取得
		$content = ( new PostContent( $post_ID ) )->getCommentRemoved();

		// 有料部分のコンテンツを取得
		$paid_content = ( new ContentFilter( $content ) )->getPaid();

		// 有料部分のコンテンツが取得できなかった場合はnullを返す
		if ( null === $paid_content ) {
			return null;
		}

		// 有料部分のコンテンツの文字数と画像数を取得
		$analyzer        = new ContentAnalyzer( $paid_content );
		$character_count = $analyzer->getCharacterCount();
		$image_count     = $analyzer->getImageCount();

		return new SellingPostContentInfoType( $character_count, $image_count );
	}
}
