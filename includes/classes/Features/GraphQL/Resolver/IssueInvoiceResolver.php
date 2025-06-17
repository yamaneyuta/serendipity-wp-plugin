<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\InitCrawledBlockNumber;
use Cornix\Serendipity\Core\Application\UseCase\IssueInvoice;
use Cornix\Serendipity\Core\Application\UseCase\SignInvoice;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Factory\AppContractRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\ChainRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\InvoiceRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\PostRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\TokenRepositoryFactory;

class IssueInvoiceResolver extends ResolverBase {

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
			$app_contract_repository = ( new AppContractRepositoryFactory( $wpdb ) )->create();
			$chain_repository        = ( new ChainRepositoryFactory( $wpdb ) )->create();
			$token_repository        = ( new TokenRepositoryFactory( $wpdb ) )->create();
			$invoice_repository      = ( new InvoiceRepositoryFactory( $wpdb ) )->create();
			$post_repository         = ( new PostRepositoryFactory( $wpdb ) )->create();

			$wpdb->query( 'START TRANSACTION' );
			// invoiceを発行
			$invoice = ( new IssueInvoice( $token_repository, $invoice_repository, $post_repository ) )->handle( $post_ID, $chain_ID, $token_address, $consumer_address );
			// 発行したinvoiceに署名を行う
			$signed_data = ( new SignInvoice( $wpdb ) )->handle( $invoice );
			// クロール済みブロック番号を初期化
			( new InitCrawledBlockNumber( $app_contract_repository, $chain_repository ) )->handle( $chain_ID );
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
