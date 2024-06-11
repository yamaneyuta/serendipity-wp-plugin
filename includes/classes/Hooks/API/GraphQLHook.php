<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\API;

use Cornix\Serendipity\Core\Lib\Logger\Logger;
use Cornix\Serendipity\Core\Lib\Plugin\Plugin;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

/**
 * GraphQLのAPI登録
 */
class GraphQLHook {

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'addActionRestApiInit' ) );
	}

	public function addActionRestApiInit(): void {

		register_rest_route(
			// 名前空間はプラグインのテキストドメインを使用
			// 外部サイトなど、第三者からのアクセスは想定していないためバージョニングは行わない
			Plugin::textDomain(),
			'/graphql',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					try {
						return $this->callback( $request );
					} catch ( \Exception $e ) {
						Logger::error( $e );
						// クライアント側で詳細なエラーが表示されないように汎用的なエラーメッセージを返す。
						return new \WP_Error( 'internal_server_error', 'Internal Server Error', array( 'status' => 500 ) );
					}
				},
				'permission_callback' => '__return_true',
			)
		);
	}

	public function callback( $request ) {

		// GraphQLサンプル
		$queryType = new ObjectType(
			array(
				'name'   => 'Query',
				'fields' => array(
					'echo' => array(
						'type'    => Type::string(),
						'args'    => array(
							'message' => Type::nonNull( Type::string() ),
						),
						'resolve' => fn ( $rootValue, array $args ): string => $rootValue['prefix'] . $args['message'],
					),
				),
			)
		);
		$schema    = new Schema(
			array(
				'query' => $queryType,
			)
		);

		// リクエストボディをデコード
		$input          = json_decode( $request->get_body(), true );
		$query          = $input['query'];
		$variableValues = isset( $input['variables'] ) ? $input['variables'] : null;

		$rootValue = array( 'prefix' => 'You said: ' );
		$result    = GraphQL::executeQuery( $schema, $query, $rootValue, null, $variableValues );
		$output    = $result->toArray();

		return $output;
	}
}
