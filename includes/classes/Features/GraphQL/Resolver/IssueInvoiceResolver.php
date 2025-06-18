<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\InitCrawledBlockNumber;
use Cornix\Serendipity\Core\Application\UseCase\IssueInvoice;
use Cornix\Serendipity\Core\Application\UseCase\SignInvoice;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class IssueInvoiceResolver extends ResolverBase {

	public function __construct(
		AppContractRepository $app_contract_repository,
		ChainRepository $chain_repository,
		TokenRepository $token_repository,
		InvoiceRepository $invoice_repository,
		PostRepository $post_repository
	) {
		$this->app_contract_repository = $app_contract_repository;
		$this->chain_repository        = $chain_repository;
		$this->token_repository        = $token_repository;
		$this->invoice_repository      = $invoice_repository;
		$this->post_repository         = $post_repository;
	}

	private AppContractRepository $app_contract_repository;
	private ChainRepository $chain_repository;
	private TokenRepository $token_repository;
	private InvoiceRepository $invoice_repository;
	private PostRepository $post_repository;

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID          = $args['postID'];
		$chain_ID         = new ChainID( $args['chainID'] );
		$token_address    = new Address( $args['tokenAddress'] );
		$consumer_address = new Address( $args['consumerAddress'] ); // 購入者のアドレス

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// 請求書番号を発行(+現在の販売価格を記録)
		global $wpdb;
		try {
			$wpdb->query( 'START TRANSACTION' );
			// invoiceを発行
			$invoice = ( new IssueInvoice( $this->token_repository, $this->invoice_repository, $this->post_repository ) )->handle( $post_ID, $chain_ID, $token_address, $consumer_address );
			// 発行したinvoiceに署名を行う
			$signed_data = ( new SignInvoice( $wpdb ) )->handle( $invoice );
			// クロール済みブロック番号を初期化
			( new InitCrawledBlockNumber( $this->app_contract_repository, $this->chain_repository ) )->handle( $chain_ID );
			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		return array(
			'invoiceIdHex'     => $invoice->id()->hex(),
			'nonce'            => $invoice->nonce()->value(),
			'serverMessage'    => $signed_data->message(),
			'serverSignature'  => $signed_data->signature(),
			'paymentAmountHex' => $invoice->paymentAmountHex(),
		);
	}
}
