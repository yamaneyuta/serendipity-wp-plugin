<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Lib\Logger\DeprecatedLogger;

class SellingPriceResolver extends ResolverBase {

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

		// 販売価格をテーブルから取得して返す
		$selling_price = $this->post_repository->get( $post_ID )->sellingPrice();

		if ( is_null( $selling_price ) ) {
			DeprecatedLogger::warn( '[57B6E802] Selling price is null for post ID: ' . $post_ID );
		}

		return is_null( $selling_price ) ? null : array(
			'amountHex' => $selling_price->amountHex(),
			'decimals'  => $selling_price->decimals(),
			'symbol'    => $selling_price->symbol(),
		);
	}
}
