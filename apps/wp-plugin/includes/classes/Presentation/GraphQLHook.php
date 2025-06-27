<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Presentation\GraphQL\RootValue;
use Cornix\Serendipity\Core\Lib\GraphQL\PluginSchema;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use DI\Container;
use GraphQL\GraphQL;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook {

	public function __construct( RestProperty $rest_property, Container $container ) {
		$this->rest_property = $rest_property;
		$this->container     = $container;
		$this->app_logger    = $container->get( AppLogger::class );
	}

	/** @var RestProperty */
	private $rest_property;
	/** @var Container */
	private $container;
	/** @var AppLogger */
	private $app_logger;

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
		$root_value = ( new RootValue() )->get( $this->container );

		$result = GraphQL::executeQuery( $schema, $query, $root_value, null, $variable_values )
			// https://webonyx.github.io/graphql-php/error-handling/#custom-error-handling-and-formatting
			->setErrorsHandler(
				function ( array $errors, callable $formatter ): array {
					foreach ( $errors as $error ) {
						$this->app_logger->error( $error ); // エラーログを出力
					}
					return array_map( $formatter, $errors );
				}
			)
			->toArray();

		return $result;
	}
}
