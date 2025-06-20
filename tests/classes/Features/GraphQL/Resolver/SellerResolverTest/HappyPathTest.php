<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Features\GraphQL\Resolver\SellerResolverTest;

use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;
use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;
use Cornix\Serendipity\Test\UseCase\RegisterSeller;
use HardhatSignerFactory;

class HappyPathTest extends UnitTestCaseBase {

	public function setUp(): void {
		parent::setUp();
		self::resetDatabase(); // データベースをリセット
		$this->register_seller = $this->container()->get( RegisterSeller::class );
	}

	private RegisterSeller $register_seller;

	/**
	 * 販売者の署名データが存在しない時にsellerフィールドを取得しても値がnullであることを確認
	 *
	 * @test
	 * @testdox [1D1BE530][GraphQL] Request seller success(sign not exists)
	 */
	public function withNoSignData() {
		// ARRANGE
		// Do nothing

		// ACT
		$result = $this->graphQl()->Seller()->get_data();
		$data   = $result['data'] ?? null;

		// ASSERT
		$this->assertTrue( isset( $data['seller'] ) );      // data.sellerオブジェクトは存在する
		$this->assertNull( $data['seller']['agreedTerms'] );// 署名が保存されていないので値はnullが設定されている
		$this->assertFalse( isset( $result['errors'] ) ); // エラーフィールドは存在しない
	}

	/**
	 * 販売者の署名データが存在する時にsellerフィールドを取得
	 *
	 * @test
	 * @testdox [2E0F9F28][GraphQL] Request seller success(sign exists)
	 */
	public function withSignData() {
		// ARRANGE
		$this->register_seller->handle( HardhatSignerFactory::alice() ); // aliceを販売者として登録

		// ACT
		$result = $this->graphQl()->Seller()->get_data();
		$data   = $result['data'] ?? null;

		// ASSERT
		// バージョン、メッセージ、署名が取得できる
		$this->assertInstanceOf( TermsVersion::class, new TermsVersion( $data['seller']['agreedTerms']['version'] ) );    // バージョン
		$this->assertInstanceOf( SigningMessage::class, new SigningMessage( $data['seller']['agreedTerms']['message'] ) );// メッセージ
		$this->assertInstanceOf( Signature::class, new Signature( $data['seller']['agreedTerms']['signature'] ) );        // 署名
		$this->assertFalse( isset( $result['errors'] ) ); // エラーフィールドは存在しない
	}
}
