<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Web3\PrivateKey;

class PrivateKeyTest extends IntegrationTestBase {

	/**
	 * 秘密鍵はhexの最大64文字列であることを確認(64文字固定ではない)
	 *
	 * @test
	 * @testdox [274031F6] PrivateKey::generate()
	 */
	public function testGenerate() {
		// ARRANGE
		$sut = new PrivateKey();

		// ACT
		$private_key = $sut->generate();

		// ASSERT
		$this->assertMatchesRegularExpression( '/^[0-9a-f]{1,64}$/', $private_key );
	}
}
