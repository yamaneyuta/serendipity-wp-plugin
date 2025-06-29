<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Lib\Logger\DeprecatedLogger;

class SellingPriceResolver extends ResolverBase {

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

		// 販売価格をテーブルから取得して返す
		$selling_price = $this->post_repository->get( new PostId( $post_ID ) )->sellingPrice();

		if ( is_null( $selling_price ) ) {
			DeprecatedLogger::warn( '[57B6E802] Selling price is null for post ID: ' . $post_ID );
		}

		return is_null( $selling_price ) ? null : array(
			'amount' => $selling_price->amount()->value(),
			'symbol' => $selling_price->symbol()->value(),
		);
	}
}
