<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\API;

use Cornix\Serendipity\Core\Lib\GraphQL\PluginSchema;
use Cornix\Serendipity\Core\Lib\GraphQL\RootValue;
use Cornix\Serendipity\Core\Lib\Logger\Logger;
use Cornix\Serendipity\Core\Lib\SystemInfo\Config;
use GraphQL\GraphQL;

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
			( new Config() )->getPluginInfo( 'TextDomain' ),
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
