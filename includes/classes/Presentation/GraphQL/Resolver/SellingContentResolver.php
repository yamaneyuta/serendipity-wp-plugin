<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Lib\Logger\DeprecatedLogger;

class SellingContentResolver extends ResolverBase {

	public function __construct(
		PostRepository $post_repository,
		UserAccessChecker $user_access_checker
	) {
		$this->post_repository     = $post_repository;
		$this->user_access_checker = $user_access_checker;
	}

	private PostRepository $post_repository;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 *
	 * @return array|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_ID );

		// 有料部分のコンテンツを取得
		$paid_content = $this->post_repository->get( new PostId( $post_ID ) )->paidContent();

		// 有料部分のコンテンツが取得できなかった場合はnullを返す
		if ( null === $paid_content ) {
			DeprecatedLogger::warn( '[248F67EA] Paid content is null for post ID: ' . $post_ID );
			return null;
		}

		return array(
			'characterCount' => $paid_content->characterCount(),
			'imageCount'     => $paid_content->imageCount(),
		);
	}
}
