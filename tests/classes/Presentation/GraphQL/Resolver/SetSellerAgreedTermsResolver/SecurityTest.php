<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Presentation\GraphQL\Resolver\SetSellerAgreedTermsResolver;

use Cornix\Serendipity\Core\Domain\Service\SellerService;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
use Cornix\Serendipity\Core\Infrastructure\Factory\TermsServiceFactory;
use Cornix\Serendipity\Test\Entity\WpUser;

class SecurityTest extends SetSellerAgreedTermsResolverBase {
	public static function setUpBeforeClass(): void {
		self::resetDatabase();
	}

	public function setUp(): void {
		parent::setUp();
		$this->setAppLogLevel( \Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel::none() );
	}

	/**
	 * 管理者以外は署名データを保存できないことを確認
	 *
	 * @test
	 * @testdox [9989CB11][GraphQL] Fail request set seller agreed terms - user: $user
	 * @dataProvider requestInvalidUsersProvider
	 */
	public function requestSetSellerFail( WpUser $user ) {
		$seller_service       = $this->container()->get( SellerService::class );
		$wallet_service       = $this->container()->get( WalletService::class );
		$terms_service        = ( new TermsServiceFactory() )->create();
		$current_seller_terms = $terms_service->getCurrentSellerTerms();
		$alice                = \HardhatSignerFactory::alice();
		$signature            = $wallet_service->signMessage( $alice, $current_seller_terms->message() );
		$this->assertNull( $seller_service->getSellerSignedTerms() );

		$variables = array(
			'version'   => $current_seller_terms->version()->value(),
			'signature' => $signature->value(),
		);
		$data      = $this->graphQl( $user )->request( self::SET_SELLER_AGREED_TERMS_MUTATION, $variables )->get_data();

		$this->assertTrue( isset( $data['errors'] ) );
		$this->assertNull( $seller_service->getSellerSignedTerms() );
	}

	public function requestInvalidUsersProvider(): array {
		return array(
			array( WpUser::contributor() ),
			array( WpUser::anotherContributor() ),
			array( WpUser::visitor() ),
		);
	}
}
