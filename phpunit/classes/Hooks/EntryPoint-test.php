<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Hooks\EntryPoint;

class EntryPointTestBase extends WP_UnitTestCase {

	// #[\Override]
	public function setUp(): void {
		parent::setUp();
		// Your own additional setup.
	}

	// #[\Override]
	public function tearDown(): void {
		// Your own additional tear down.
		parent::tearDown();
	}


	/**
	 * @test
	 * @testdox [35783EEB] Hooks登録時にエラーが発生しないことを確認
	 * @dataProvider checkNoExceptionDataProvider
	 */
	public function checkNoException( $screen ) {

		if ( ! is_null( $screen ) ) {
			set_current_screen( $screen );
		}

		$error = null;
		try {
			new EntryPoint(); // Hooksを登録

			// 各種アクションを実行
			do_action( 'rest_api_init' );
			do_action( 'enqueue_block_assets' );
		} catch ( Throwable $e ) {
			$error = $e;
		}

		$this->assertNull( $error );
	}

	public function checkNoExceptionDataProvider(): array {
		// screen, ...
		return array(
			array( null ),
			array( 'edit-post' ),
		);
	}
}
