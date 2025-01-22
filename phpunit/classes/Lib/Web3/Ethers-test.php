<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\Signer;

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
		$this->assertMatchesRegularExpression( '/^0x0{40}$/', Ethers::zeroAddress() );
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
		$this->assertEquals( $signer->address(), $expected_address );

		// ACT
		$address = Ethers::verifyMessage( $message, $signature, $signer->address() );

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

	/**
	 * ウォレットアドレスがチェックサム付で変換されることを確認
	 *
	 * @test
	 * @testdox [A164358E] Ethers::getAddress() - address: $address -> $expected
	 * @dataProvider getAddressDataProvider
	 */
	public function testGetAddress( string $address, string $expected ) {
		// ARRANGE
		// Do nothing

		// ACT
		$actual = Ethers::getAddress( $address );

		// ASSERT
		$this->assertEquals( $expected, $actual );
	}
	public function getAddressDataProvider() {
		return array(
			array( '0xf39fd6e51aad88f6f4ce6ab8827279cfffb92266', '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266' ), // 全部小文字
			array( '0xF39FD6E51AAD88F6F4CE6AB8827279CFFFB92266', '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266' ), // 全部大文字(通常あり得ないが、正常に変換できることを確認)
			array( '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266' ), // 元と同じ
		);
	}

	/**
	 * ウォレットアドレスが正しい形式かどうかを判定するメソッドが正常に動作することを確認
	 *
	 * @test
	 * @testdox [83B1DA31] Ethers::isAddress() - address: $address -> $expected
	 * @dataProvider isAddressDataProvider
	 */
	public function testIsAddress( string $address, bool $expected ) {
		// ARRANGE
		// Do nothing

		// ACT
		$actual = Ethers::isAddress( $address );

		// ASSERT
		$this->assertEquals( $expected, $actual );
	}
	public function isAddressDataProvider(): array {
		return array(
			// 正しいウォレットアドレス
			array( '0xf39fd6e51aad88f6f4ce6ab8827279cfffb92266', true ), // すべて小文字
			array( '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', true ), // 正しいチェックサム
			array( Ethers::zeroAddress(), true ), // ゼロアドレス

			// 不正なウォレットアドレス
			array( '0xF39Fd6e51aad88F6F4ce6aB8827279cffFb92266', false ),   // 不正なチェックサム(先頭のfが大文字になっている)
			array( '0xF39Fd6e51aad88F6F4ce6aB8827279cffFb9226', false ),    // 桁数が足りない
			array( 'f39Fd6e51aad88F6F4ce6aB8827279cffFb92266', false ),     // 先頭の0xがない
		);
	}
}
