<?php

class SampleTest extends WP_UnitTestCase {

	// #[\Override]
	public function setUp(): void {
		parent::setUp();
		// Your own additional setup.

		$this->administrator = 1;
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );
	}

	/** @var int */
	protected $administrator;
	protected $server;

	// #[\Override]
	public function tearDown(): void {
		global $wp_rest_server;
		$wp_rest_server = null;

		// Your own additional tear down.
		parent::tearDown();
	}

	/**
	 * @test
	 * @testdox [XXXXXX] GraphQLのテスト
	 */
	public function substr() {
		wp_set_current_user( $this->administrator );

		$request      = new WP_REST_Request( 'POST', '/todo-list/graphql' );
		$graphql_data = array(
			'query'     => 'query echo($message: String!) { echo(message: $message) }',
			'variables' => array( 'message' => 'hello typescript client' ),
		);
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $graphql_data ) );
		// $request->set_body_params($graphql_data); // 使用不可

		/** @var WP_REST_Response */
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals(
			array(
				'data' => array(
					'echo' => 'is_admin: true - You said: hello typescript client',
				),
			),
			$response->get_data()
		);
	}
}
