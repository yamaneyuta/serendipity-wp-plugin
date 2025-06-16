<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Infrastructure\Factory\InvoiceRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\TermsServiceFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\TokenRepositoryFactory;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepository;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Calc\PriceExchange;
use wpdb;

class IssueInvoice {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	public function handle( int $post_ID, ChainID $chain_ID, Address $payment_token_address, Address $consumer_address ): Invoice {
		$token_repository   = ( new TokenRepositoryFactory( $this->wpdb ) )->create();
		$invoice_repository = ( new InvoiceRepositoryFactory( $this->wpdb ) )->create();

		$payment_token  = ( new GetPaymentToken( $token_repository ) )->handle( $chain_ID, $payment_token_address ); // 支払トークン
		$seller_address = ( new GetSellerAddress() )->handle();  // 販売者アドレス
		$selling_price  = ( new GetSellingPrice( $this->wpdb ) )->handle( $post_ID ); // 販売価格
		if ( is_null( $selling_price ) ) {
			throw new \InvalidArgumentException( '[8AF88CAF] Selling price is null for post ID: ' . $post_ID );
		}

		// 支払うトークンにおける価格を計算
		// ※ これは`1ETH`等の価格を表現するオブジェクトであり、実際に支払う数量(wei等)ではないことに注意
		$payment_price = ( new PriceExchange() )->convert( $selling_price, $payment_token->symbol() );
		// 支払うトークン量を取得
		$payment_amount_hex = $payment_price->toTokenAmount( $chain_ID );

		$invoice = new Invoice(
			InvoiceID::generate(), // 新規請求書ID
			$post_ID,
			$chain_ID,
			$selling_price,
			$seller_address,
			$payment_token_address,
			$payment_amount_hex,
			$consumer_address,
			InvoiceNonce::generate() // 新規nonce
		);
		assert( null === $invoice_repository->get( $invoice->id() ), '[A9E90E49] Duplicate invoice ID detected.' );

		// 請求書情報を保存
		$invoice_repository->add( $invoice );

		return $invoice;
	}
}

/**
 * 指定されたチェーンID、アドレスのトークン情報を取得します。
 *
 * @internal
 */
class GetPaymentToken {
	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository = $token_repository;
	}

	private TokenRepository $token_repository;

	public function handle( ChainID $chain_ID, Address $token_address ): Token {
		$token = $this->token_repository->get( $chain_ID, $token_address );
		if ( is_null( $token ) || ! $token->isPayable() ) {
			throw new \InvalidArgumentException( '[9213F631] The specified token is not payable.' );
		}
		return $token;
	}
}

/**
 * 販売者のアドレスを取得します。
 *
 * @internal
 */
class GetSellerAddress {
	public function handle(): Address {
		$seller_singed_terms = ( new TermsServiceFactory() )->create()->getSignedSellerTerms();
		assert( $seller_singed_terms, '[88C95394] SellerAgreedTerms not found' );
		return Ethers::verifyMessage( $seller_singed_terms->terms()->message(), $seller_singed_terms->signature() );
	}
}
