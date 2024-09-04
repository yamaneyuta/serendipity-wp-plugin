<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\BlockName;

class BlockNameTest extends WP_UnitTestCase {
	/**
	 * ブロック名を取得できることを確認
	 *
	 * @test
	 * @testdox [DA581D34] BlockName::get
	 */
	public function testGet() {
		// ARRANGE
		// Do nothing.

		// ACT
		$block_name = BlockName::get();

		// ASSERT
		$this->assertNotEmpty( $block_name );
	}
}
