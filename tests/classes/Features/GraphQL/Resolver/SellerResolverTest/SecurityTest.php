<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Features\GraphQL\Resolver\SellerResolverTest;

use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;
use Cornix\Serendipity\Test\Entity\WpUser;
use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;
use Cornix\Serendipity\Test\UseCase\RegisterSeller;
use HardhatSignerFactory;

class SecurityTest extends UnitTestCaseBase {

	public function setUp(): void {
		parent::setUp();
		self::resetDatabase(); // データベースをリセット
		$this->register_seller = self::container()->get( RegisterSeller::class );
	}

	private RegisterSeller $register_seller;

	/**
	 * 誰でも`seller`の呼び出しが可能であることを確認
	 *
	 * @test
	 * @testdox [][GraphQL] Request seller success - user: $user
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestSellerSuccessWithNoSignData( WpUser $user ) {
		// ARRANGE
		$this->register_seller->handle( HardhatSignerFactory::alice() ); // aliceを販売者として登録

		// ACT
		$result = $this->graphQl( $user )->Seller()->get_data();
		$data   = $result['data'] ?? null;

		// ASSERT
		// バージョン、メッセージ、署名が取得できる
		$this->assertInstanceOf( TermsVersion::class, new TermsVersion( $data['seller']['agreedTerms']['version'] ) );    // バージョン
		$this->assertInstanceOf( SigningMessage::class, new SigningMessage( $data['seller']['agreedTerms']['message'] ) );// メッセージ
		$this->assertInstanceOf( Signature::class, new Signature( $data['seller']['agreedTerms']['signature'] ) );        // 署名
		$this->assertFalse( isset( $result['errors'] ) ); // エラーフィールドは存在しない
	}
	public function requestValidUsersProvider(): array {
		// 誰でも`seller`の呼び出しが可能
		return array(
			array( WpUser::admin() ),
			array( WpUser::contributor() ),
			array( WpUser::anotherContributor() ),
			array( WpUser::visitor() ),
		);
	}
}
