<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Lib\Logger\Logger;

class SellingContentResolver extends ResolverBase {

	public function __construct( PostRepository $post_repository ) {
		$this->post_repository = $post_repository;
	}

	private PostRepository $post_repository;

	/**
	 * #[\Override]
	 *
	 * @return array|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// 有料部分のコンテンツを取得
		$paid_content = $this->post_repository->get( $post_ID )->paidContent();

		// 有料部分のコンテンツが取得できなかった場合はnullを返す
		if ( null === $paid_content ) {
			Logger::warn( '[248F67EA] Paid content is null for post ID: ' . $post_ID );
			return null;
		}

		return array(
			'characterCount' => $paid_content->characterCount(),
			'imageCount'     => $paid_content->imageCount(),
		);
	}
}
