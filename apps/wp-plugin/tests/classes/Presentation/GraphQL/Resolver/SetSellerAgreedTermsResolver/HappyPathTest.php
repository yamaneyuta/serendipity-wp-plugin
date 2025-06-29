<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\Presentation\GraphQL\Resolver\SetSellerAgreedTermsResolver;

use Cornix\Serendipity\Core\Domain\Service\SellerService;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
use Cornix\Serendipity\Core\Infrastructure\Factory\TermsServiceFactory;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\TestLib\Entity\WpUser;
use HardhatSignerFactory;

class HappyPathTest extends SetSellerAgreedTermsResolverBase {
	public static function setUpBeforeClass(): void {
		self::resetDatabase();
	}

	/**
	 * 管理者が署名データを保存できることを確認
	 *
	 * @test
	 * @testdox [D48AB2DA][GraphQL] Success request set seller agreed terms - user: $user
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestSetSellerSuccess( WpUser $user ) {
		$seller_service       = $this->container()->get( SellerService::class );
		$wallet_service       = $this->container()->get( WalletService::class );
		$terms_service        = ( new TermsServiceFactory() )->create();
		$current_seller_terms = $terms_service->getCurrentSellerTerms();
		$alice                = HardhatSignerFactory::alice();
		$signature            = $wallet_service->signMessage( $alice, $current_seller_terms->message() );
		$this->assertNull( $seller_service->getSellerSignedTerms() );

		$variables = array(
			'version'   => $current_seller_terms->version()->value(),
			'signature' => $signature->value(),
		);
		$data      = $this->graphQl( $user )->request( self::SET_SELLER_AGREED_TERMS_MUTATION, $variables )->get_data();

		$this->assertFalse( isset( $data['errors'] ) );
		$signed_seller_terms = $seller_service->getSellerSignedTerms();
		$this->assertTrue( $signed_seller_terms->terms()->version()->equals( $current_seller_terms->version() ) );
		$this->assertTrue( $signed_seller_terms->terms()->message()->value() === $current_seller_terms->message()->value() );
		$this->assertEquals( $alice->address(), Ethers::verifyMessage( $signed_seller_terms->terms()->message(), $signed_seller_terms->signature() ) );
	}

	public function requestValidUsersProvider(): array {
		return array(
			array( WpUser::admin() ),
		);
	}
}
