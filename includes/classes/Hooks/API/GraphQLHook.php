<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\API;

use Cornix\Serendipity\Core\Features\GraphQL\RootValue;
use Cornix\Serendipity\Core\Lib\GraphQL\PluginSchema;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use GraphQL\GraphQL;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook {

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {

		$rest_property = new RestProperty();

		// GraphQLのエンドポイントを登録
		$success = register_rest_route(
			$rest_property->namespace(),
			$rest_property->graphQLRoute(),
			array(
				'methods'             => 'POST',
				'callback'            => fn ( $request ) => $this->callback( $request ),
				'permission_callback' => '__return_true',
			)
		);

		assert( $success );
	}

	public function callback( $request ) {
		// リクエストボディをデコード
		$input           = json_decode( $request->get_body(), true );
		$query           = $input['query'];
		$variable_values = isset( $input['variables'] ) ? $input['variables'] : null;

		$schema     = ( new PluginSchema() )->get();
		$root_value = ( new RootValue() )->get();

		$result = GraphQL::executeQuery( $schema, $query, $root_value, null, $variable_values );
		$output = $result->toArray();

		return $output;
	}
}
