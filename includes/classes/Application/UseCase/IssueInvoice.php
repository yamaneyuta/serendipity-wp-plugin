<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Service\SellerService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\Lib\Calc\PriceExchange;

class IssueInvoice {
	public function __construct( TokenRepository $token_repository, InvoiceRepository $invoice_repository, PostRepository $post_repository, SellerService $seller_service ) {
		$this->token_repository   = $token_repository;
		$this->invoice_repository = $invoice_repository;
		$this->post_repository    = $post_repository;
		$this->seller_service     = $seller_service;
	}
	private TokenRepository $token_repository;
	private InvoiceRepository $invoice_repository;
	private PostRepository $post_repository;
	private SellerService $seller_service;

	public function handle( int $post_ID, ChainID $chain_ID, Address $payment_token_address, Address $consumer_address ): Invoice {
		$payment_token  = ( new GetPaymentToken( $this->token_repository ) )->handle( $chain_ID, $payment_token_address ); // 支払トークン
		$seller_address = $this->seller_service->getSellerAddress();  // 販売者アドレス
		$selling_price  = $this->post_repository->get( $post_ID )->sellingPrice();
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
		assert( null === $this->invoice_repository->get( $invoice->id() ), '[A9E90E49] Duplicate invoice ID detected.' );

		// 請求書情報を保存
		$this->invoice_repository->save( $invoice );

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
