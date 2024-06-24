<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Features\Settings\PostSettings;
use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Lib\SystemInfo\PluginSettings;
use Cornix\Serendipity\Core\Types\Price;

class PostSellingPriceResolver extends ResolverBase {

	public function __construct( PluginSettings $plugin_settings ) {
		parent::__construct( 'postSellingPrice' );
		$this->plugin_settings = $plugin_settings;
	}

	private PluginSettings $plugin_settings;

	/**
	 * #[\Override]
	 *
	 * @return Price|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿が公開済み、または編集可能な権限がある時に設定されている価格を返します。
		if ( ( new PostSettings() )->isPublished( $post_ID ) || ( new Access() )->canCurrentUserEditPost( $post_ID ) ) {
			return $this->plugin_settings->getPostSellingPrice( $post_ID );
		}

		throw new \LogicException( 'You do not have permission to access this post.' );
	}
}
