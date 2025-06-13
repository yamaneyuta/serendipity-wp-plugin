<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Entity\Signer;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;

class EthersTest extends IntegrationTestBase {

	/**
	 * ゼロアドレスが160ビットの16進数であることを確認
	 *
	 * @test
	 * @testdox [41CCE6CF] Ethers::zeroAddress
	 */
	public function testZeroAddress() {
		// ARRANGE
		// Do nothing

		// ACT
		// Do nothing

		// ASSERT
		$this->assertMatchesRegularExpression( '/^0x0{40}$/', (string) Ethers::zeroAddress() );
	}

	/**
	 * 署名とメッセージからウォレットアドレスが計算できることを確認
	 *
	 * @test
	 * @testdox [DF2CA58C] Signer::address() - address: $expected_address
	 * @dataProvider verifyMessageDataProvider
	 */
	public function testVerifyMessage( string $expected_address, string $private_key ) {
		// ARRANGE
		$signer    = new Signer( $private_key );
		$message   = 'Hello, world!';
		$signature = $signer->signMessage( $message );
		$this->assertEquals( $signer->address()->value(), $expected_address );

		// ACT
		$address = Ethers::verifyMessage( $message, $signature );

		// ASSERT
		$this->assertEquals( $expected_address, $address );
	}
	public function verifyMessageDataProvider() {
		// ウォレットと秘密鍵の組み合わせ ※ 本番環境での使用禁止
		// [ wallet_address, private_key ]
		return array(
			// hardhatで使用するテスト用ウォレット
			array( '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', 'ac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80' ),
			// 先頭が'0x0000'の特殊なウォレット
			array( '0x0000fFBf843CEC9A426544dFEffFA9f1a2200531', 'cd17a0fcd2949a80bf9e22bebda76f34c71d242db27dfe1d29e5effea44b8379' ),
			// 末尾が'0000'の特殊なウォレット
			array( '0x0b40f1dBA8bB2f3EbD6e2641DBb981e963De0000', '379a98880f566e9bd1a484e2240b03e525f217d1c46884f92d1df6f5cc403a5d' ),
		);
	}
}
