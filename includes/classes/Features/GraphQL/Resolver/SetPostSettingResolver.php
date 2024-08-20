<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\PostSetting;
use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Types\PostSettingType;
use Cornix\Serendipity\Core\Types\PriceType;
use wpdb;

class SetPostSettingResolver extends ResolverBase {

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	/**
	 * #[\Override]
	 *
	 * @return PostSettingType|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];
		// 編集可能な権限がない場合は例外をスロー。
		if ( ! ( new Access() )->canCurrentUserEditPost( $post_ID ) ) {
			throw new \LogicException( '[5EF691C0] You do not have permission to edit this post.' );
		}

		$post_setting = new PostSettingType(
			new PriceType(
				$args['postSetting']['sellingPrice']['amountHex'],
				$args['postSetting']['sellingPrice']['decimals'],
				$args['postSetting']['sellingPrice']['symbol']
			),
			$args['postSetting']['sellingNetwork']
		);

		( new PostSetting( $this->wpdb ) )->set( $post_ID, $post_setting );
		return true;
	}
}
