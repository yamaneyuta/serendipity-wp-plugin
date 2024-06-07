<?php

use Yoast\WPTestUtils\BrainMonkey\TestCase;

class ExampleTest extends TestCase {

	protected function set_up() {
		parent::set_up();
		// Your own additional setup.
	}

	protected function tear_down() {
		// Your own additional tear down.
		parent::tear_down();
	}

	/**
	 * @test
	 */
	public function example() {

		update_option( 'foo', 1 );
		$foo = get_option( 'foo', 0 );
		$this->assertEquals( 1, $foo );

		$this->assertTrue( true );
	}
}
