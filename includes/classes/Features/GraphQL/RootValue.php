<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\PostSellingPriceResolver;
use Cornix\Serendipity\Core\Lib\SystemInfo\PluginSettings;

class RootValue {

	public function __construct( PluginSettings $plugin_settings ) {
		$this->plugin_settings = $plugin_settings;
	}

	private PluginSettings $plugin_settings;

	/**
	 * @return array<string, mixed>
	 */
	public function get() {

		$resolvers = array(
			new PostSellingPriceResolver( $this->plugin_settings ),
		);

		$result = array();
		foreach ( $resolvers as $resolver ) {
			$result[ $resolver->field() ] = function ( array $root_value, array $args ) use ( $resolver ) {
				return $resolver->resolve( $root_value, $args );
			};
		}

		return $result;
	}
}
