<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Types\PriceType;
use Cornix\Serendipity\Core\Types\PostSettingType;

class SellingNetworkResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return PriceType|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿が公開済み、または編集可能な権限がある時に設定されている販売ネットワーク種別を返します。
		if ( $this->isPublishedOrEditable( $post_ID ) ) {
			// 投稿設定を取得
			/** @var PostSettingType|null */
			$post_setting = $root_value['postSetting']( $root_value, array( 'postID' => $post_ID ) );
			return $post_setting ? $post_setting->sellingNetwork : null;
		}

		throw new \LogicException( '[A9085BAC] You do not have permission to access this post.' );
	}
}
