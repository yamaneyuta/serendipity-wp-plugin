<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\PostSetting;
use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Lib\SystemInfo\WPSettings;
use Cornix\Serendipity\Core\Types\PostSettingType;
use wpdb;

class PostSettingResolver extends ResolverBase {

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

		// 投稿設定を取得します。
		return ( new PostSetting( $this->wpdb ) )->get( $post_ID );
	}
}
