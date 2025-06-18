<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\Service;

use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use Cornix\Serendipity\Core\Presentation\GraphQLHook;
use Cornix\Serendipity\Test\Entity\WpUser;
use Cornix\Serendipity\Test\Util\WithUser;
use DI\Container;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * クライアントからのリクエストを疑似的に行うためのサービス
 */
class ClientRequestService {

	public function __construct( Container $container ) {
		$this->container = $container;
	}
	private Container $container;

	public function setUp(): void {
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		( new GraphQLHook( new RestPropertyStub(), $this->container ) )->register();
		do_action( 'rest_api_init' );
	}
	public function tearDown(): void {
		global $wp_rest_server;
		$wp_rest_server = null;
	}

	public function createGraphQlRequester( ?WpUser $execute_user ): GraphQlRequester {
		return new GraphQlRequester( $execute_user );
	}
}


class RestPropertyStub extends RestProperty {
	/** @inheritdoc */
	public function namespace(): string {
		return 'phpunit'; // テスト用の名前空間を返す
	}
}


class GraphQlRequester {

	public function __construct( ?WpUser $execute_user ) {
		$this->execute_user = $execute_user;
	}
	private ?WpUser $execute_user = null;

	private function request( string $query, array $variables = null ): WP_REST_Response {
		$with_user = null !== $this->execute_user ? new WithUser( $this->execute_user ) : null;

		$request_data = array(
			'query' => $query,
		);
		if ( $variables ) {
			$request_data['variables'] = $variables;
		}

		$rest_property = new RestPropertyStub();
		$namespace     = $rest_property->namespace();
		$graphQlRoute  = $rest_property->graphQlRoute();
		$request       = new WP_REST_Request( 'POST', "/{$namespace}{$graphQlRoute}" );

		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( $request_data ) );

		global $wp_rest_server;
		$response = $wp_rest_server->dispatch( $request );

		return $response;
	}

	public function Seller() {
		return $this->request( GraphQlQuery::Seller );
	}
}

class GraphQlQuery {
	public const Seller = <<<GRAPHQL
		query Seller {
			seller {
				agreedTerms {
					version
					message
					signature
				}
			}
		}
		GRAPHQL;
}
