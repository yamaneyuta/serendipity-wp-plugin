<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\API;

use Cornix\Serendipity\Core\Features\GraphQL\RootValue;
use Cornix\Serendipity\Core\Lib\GraphQL\PluginSchema;
use Cornix\Serendipity\Core\Lib\Logger\Logger;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use GraphQL\GraphQL;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook {

	public function __construct( RestProperty $rest_property ) {
		$this->rest_property = $rest_property;
	}

	/** @var RestProperty */
	private $rest_property;

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {

		// GraphQLのエンドポイントを登録
		$success = register_rest_route(
			$this->rest_property->namespace(),
			$this->rest_property->graphQlRoute(),
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
		$root_value = ( new RootValue() )->get();

		$result = GraphQL::executeQuery( $schema, $query, $root_value, null, $variable_values )
			// https://webonyx.github.io/graphql-php/error-handling/#custom-error-handling-and-formatting
			->setErrorsHandler(
				function ( array $errors, callable $formatter ): array {
					foreach ( $errors as $error ) {
						// エラーログを出力
						Logger::error( $error );
					}
					return array_map( $formatter, $errors );
				}
			)
			->toArray();

		return $result;
	}
}
