<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\Database\PostSetting;
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

		// 投稿が公開済み、または編集可能な権限がある時に設定されている価格を返します。
		if ( ( new WPSettings() )->isPublished( $post_ID ) || ( new Access() )->canCurrentUserEditPost( $post_ID ) ) {
			return ( new PostSetting( $this->wpdb ) )->get( $post_ID );
		}

		throw new \LogicException( '[FB5A5BB1] You do not have permission to access this post.' );
	}
}
