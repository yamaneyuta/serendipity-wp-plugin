<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Constant\NetworkCategoryID;
use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Repository\TokenRepository;
use Cornix\Serendipity\Core\Service\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Service\Factory\TermsServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
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
		$terms_service    = ( new TermsServiceFactory() )->create();
		$seller_signature = $seller->signMessage( $terms_service->getCurrentSellerTerms()->message() );
		$terms_service->saveSellerSignature( $seller_signature );
		// 署名時のメッセージハッシュを取得
		$seller_terms_message_hash = '0x' . Keccak::hash( Ethers::eip191( $terms_service->getSignedSellerTerms()->terms()->message() ), 256 );

		// 販売価格1,000円で投稿を作成
		$selling_network_category = NetworkCategory::from( NetworkCategoryID::PRIVATENET );
		$selling_price            = new Price( HexFormat::toHex( 1000 ), 0, 'JPY' );
		$post_ID                  = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => ( new SamplePostContent() )->get( $selling_network_category, $selling_price ),
			)
		);

		// プライベートネット1のETHで支払う
		$payment_chain = ( new ChainServiceFactory() )->create( $GLOBALS['wpdb'] )->getChain( ChainID::privatenet1() );
		$payment_token = ( new TokenRepository() )->get(
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

		$result = $this->requestGraphQL( $query, $params );
		$data   = $result = $result->get_data()['data'];
		assert( ! isset( $data['errors'] ) );
		$invoice_id_hex     = $data['issueInvoice']['invoiceIdHex'];
		$nonce              = $data['issueInvoice']['nonce'];
		$server_message     = $data['issueInvoice']['serverMessage'];
		$server_signature   = $data['issueInvoice']['serverSignature'];
		$payment_amount_hex = $data['issueInvoice']['paymentAmountHex'];
		$server_address     = Ethers::verifyMessage( $server_message, $server_signature );

		// TODO: Service等から値を取得
		$consumer_terms_version       = 1;    // 暫定
		$affiliate_terms_message_hash = '0x00';
		$affiliate_terms_signature    = '0x00';
		$affiliate_ratio              = 0;

		$prev_status = $sut->getPaywallStatus( $server_address, $post_ID, $consumer->address() );
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
		$paywall_status = $sut->getPaywallStatus( $server_address, $post_ID, $consumer->address() );
		$this->assertTrue( $paywall_status->isUnlocked() );
	}
}
