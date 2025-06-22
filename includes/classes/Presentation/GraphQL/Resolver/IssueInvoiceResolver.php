<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\InitCrawledBlockNumber;
use Cornix\Serendipity\Core\Application\UseCase\IssueInvoice;
use Cornix\Serendipity\Core\Application\UseCase\SignInvoice;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class IssueInvoiceResolver extends ResolverBase {

	public function __construct(
		IssueInvoice $issue_invoice,
		InitCrawledBlockNumber $init_crawled_block_number,
		UserAccessChecker $user_access_checker
	) {
		$this->issue_invoice             = $issue_invoice;
		$this->init_crawled_block_number = $init_crawled_block_number;
		$this->user_access_checker       = $user_access_checker;
	}

	private IssueInvoice $issue_invoice;
	private InitCrawledBlockNumber $init_crawled_block_number;
	private UserAccessChecker $user_access_checker;

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

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_ID );

		// 請求書番号を発行(+現在の販売価格を記録)
		global $wpdb;
		try {
			$wpdb->query( 'START TRANSACTION' );
			// invoiceを発行
			$invoice = $this->issue_invoice->handle( $post_ID, $chain_ID, $token_address, $consumer_address );
			// 発行したinvoiceに署名を行う
			$signed_data = ( new SignInvoice( $wpdb ) )->handle( $invoice );
			// クロール済みブロック番号を初期化
			$this->init_crawled_block_number->handle( $chain_ID );
			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		return array(
			'invoiceIdHex'     => $invoice->id()->hex(),
			'nonce'            => $invoice->nonce()->value(),
			'serverMessage'    => $signed_data->message()->value(),
			'serverSignature'  => $signed_data->signature()->value(),
			'paymentAmountHex' => $invoice->paymentAmountHex(),
		);
	}
}
