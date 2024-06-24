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

	public function __construct( RestProperty $rest_property, RootValue $root_value ) {
		$this->rest_property = $rest_property;
		$this->root_value    = $root_value;
	}

	/** @var RestProperty */
	private $rest_property;

	/** @var RootValue */
	private $root_value;

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {

		// GraphQLのエンドポイントを登録
		$success = register_rest_route(
			$this->rest_property->namespace(),
			$this->rest_property->graphQLRoute(),
			array(
				'methods'             => 'POST',
				'callback'            => fn ( \WP_REST_Request $request ) => $this->callback( $request ),
				'permission_callback' => '__return_true',
			)
		);

		assert( $success );
	}

	public function callback( \WP_REST_Request $request ) {
		// リクエストボディをデコード
		$input           = json_decode( $request->get_body(), true );
		$query           = $input['query'];
		$variable_values = isset( $input['variables'] ) ? $input['variables'] : null;

		$schema     = ( new PluginSchema() )->get();
		$root_value = $this->root_value->get();

		$result = GraphQL::executeQuery( $schema, $query, $root_value, null, $variable_values );
		$output = $result->toArray();

		return $output;
	}
}
