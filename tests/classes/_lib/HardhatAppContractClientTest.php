<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Application\Service\TermsService;
use Cornix\Serendipity\Core\Domain\Service\SellerService;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use kornrunner\Keccak;

class HardhatAppContractClientTest extends IntegrationTestBase {

	/**
	 * 有料記事購入までのフローのテスト
	 *
	 * @test
	 * @testdox [CECE9109] HardhatAppContractClient::unlockPaywall (ETH)
	 */
	public function testBar(): void {
		// ARRANGE
		$sut = HardhatAppContractClient::fromChainID( ChainID::privatenet1() );

		// 販売者はalice、購入者はbobとする
		$seller   = HardhatSignerFactory::alice();
		$consumer = HardhatSignerFactory::bob();

		// 販売者の署名情報を保存
		$seller_service   = $this->container()->get( SellerService::class );
		$wallet_service   = $this->container()->get( WalletService::class );
		$terms_service    = $this->container()->get( TermsService::class );
		$seller_signature = $wallet_service->signMessage( $seller, $terms_service->getCurrentSellerTerms()->message() );
		$terms_service->saveSellerSignature( $seller_signature );
		// 署名時のメッセージハッシュを取得
		$seller_terms_message_hash = '0x' . Keccak::hash( Ethers::eip191( $seller_service->getSellerSignedTerms()->terms()->message()->value() ), 256 );

		// 販売価格1,000円で投稿を作成
		$selling_network_category_id = NetworkCategoryID::privatenet();
		$selling_price               = new Price( HexFormat::toHex( 1000 ), 0, new Symbol( 'JPY' ) );
		$post_ID                     = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => ( new SamplePostContent() )->get( $selling_network_category_id, $selling_price ),
			)
		);

		// プライベートネット1のETHで支払う
		$payment_chain = ( new ChainServiceFactory() )->create()->getChain( ChainID::privatenet1() );
		$payment_token = ( new TokenRepositoryImpl() )->get(
			$payment_chain->id(),
			Ethers::zeroAddress() // ETH
		);

		$query  = <<<GRAPHQL
			mutation IssueInvoice(\$postID: Int!, \$chainID: Int!, \$tokenAddress: String!, \$consumerAddress: String!) {
				issueInvoice(postID: \$postID, chainID: \$chainID, tokenAddress: \$tokenAddress, consumerAddress: \$consumerAddress) {
					invoiceIdHex
					nonce
					serverMessage
					serverSignature
					paymentAmountHex
				}
			}
		GRAPHQL;
		$params = array(
			'postID'          => $post_ID,
			'chainID'         => $payment_token->chainID()->value(),
			'tokenAddress'    => $payment_token->address()->value(),
			'consumerAddress' => $consumer->address()->value(),
		);

		$result             = $this->requestGraphQL( $query, $params );
		$data               = $result->get_data();
		$invoice_data       = $data['data']['issueInvoice'];
		$invoice_id_hex     = $invoice_data['invoiceIdHex'];
		$nonce              = $invoice_data['nonce'];
		$server_message     = new SigningMessage( $invoice_data['serverMessage'] );
		$server_signature   = new Signature( $invoice_data['serverSignature'] );
		$payment_amount_hex = $invoice_data['paymentAmountHex'];
		$server_address     = Ethers::verifyMessage( $server_message, $server_signature );

		// TODO: Service等から値を取得
		$consumer_terms_version       = 1;    // 暫定
		$affiliate_terms_message_hash = '0x00';
		$affiliate_terms_signature    = '0x00';
		$affiliate_ratio              = 0;

		$prev_status = $sut->getPaywallStatus( $server_address, new PostId( $post_ID ), $consumer->address() );
		$this->assertFalse( $prev_status->isUnlocked() );

		// ACT
		$sut->unlockPaywall(
			$consumer,
			$server_signature,
			$seller_terms_message_hash,
			$seller_signature,
			$consumer_terms_version,
			$affiliate_terms_message_hash,
			$affiliate_terms_signature,
			InvoiceID::from( $invoice_id_hex ),
			$post_ID,
			$payment_token->address(),
			$payment_amount_hex,
			$affiliate_ratio
		);

		// ASSERT
		$paywall_status = $sut->getPaywallStatus( $server_address, new PostId( $post_ID ), $consumer->address() );
		$this->assertTrue( $paywall_status->isUnlocked() );
	}
}
