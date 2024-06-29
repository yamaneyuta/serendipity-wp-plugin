<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\PostSettingResolver;

class RootValue {

	/**
	 * @return array<string, mixed>
	 */
	public function get() {
		global $wpdb;

		$resolvers = array(
			new PostSettingResolver( $wpdb ),
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
