<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\Presentation\GraphQL\Resolver\CurrentSellerTermsResolver;

use Cornix\Serendipity\Core\Infrastructure\Factory\TermsServiceFactory;
use Cornix\Serendipity\TestLib\Entity\WpUser;

class HappyPathTest extends CurrentSellerTermsResolverBase {

	public static function setUpBeforeClass(): void {
		self::resetDatabase(); // データベースをリセット
	}


	/**
	 * 管理者は最新の販売者向け利用規約の情報を取得できることを確認
	 *
	 * @test
	 * @testdox [832BAB37][GraphQL] Request current seller terms success
	 */
	public function requestCurrentSellerTermsSuccess() {
		// ARRANGE
		$current_seller_terms = ( new TermsServiceFactory() )->create()->getCurrentSellerTerms();

		// ACT
		$data = $this->graphQl( WpUser::admin() )->request(
			self::CURRENT_SELLER_TERMS_QUERY,
			array()
		)->get_data();

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない

		// Repositoryから取得した値と一致していることを確認
		$this->assertEquals( $current_seller_terms->version()->value(), $data['data']['currentSellerTerms']['version'] );
		$this->assertEquals( $current_seller_terms->message()->value(), $data['data']['currentSellerTerms']['message'] );
	}
}
