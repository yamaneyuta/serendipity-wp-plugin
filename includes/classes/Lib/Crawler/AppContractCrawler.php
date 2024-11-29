<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Crawler;

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Lib\Convert\Padding;
use Cornix\Serendipity\Core\Lib\Repository\ServerSignerData;
use Cornix\Serendipity\Core\Lib\Repository\UnlockPaywallTransaction;
use Cornix\Serendipity\Core\Lib\Repository\UnlockPaywallTransferEvent;
use Cornix\Serendipity\Core\Lib\Web3\AppAbi;
use Cornix\Serendipity\Core\Lib\Web3\AppClientFactory;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClientFactory;
use Cornix\Serendipity\Core\Types\BlockNumberType;
use Cornix\Serendipity\Core\Types\InvoiceID;
use phpseclib\Math\BigInteger;
use stdClass;

/**
 * Appコントラクトのログを取得し、DBに保存するクラス
 */
class AppContractCrawler {
	public function __construct( \wpdb $wpdb ) {
		$this->app_abi = new AppAbi();
		$this->wpdb    = $wpdb;
	}
	private AppAbi $app_abi;
	private \wpdb $wpdb;

	public function crawl( int $chain_ID, BlockNumberType $from_block, BlockNumberType $to_block ): void {
		// UnlockPaywallTransferイベントのログを取得
		$transfer_logs = $this->getUnlockPaywallTransferLogs( $chain_ID, $from_block, $to_block );
		// トランザクション情報をDBに保存
		$this->saveUnlockPaywallTransaction( $this->wpdb, $chain_ID, $transfer_logs );
		// UnlockPaywallTransferイベントのログをDBに保存
		$this->saveUnlockPaywallTransfer( $this->wpdb, $transfer_logs );
	}

	/**
	 * UnlockPaywallTransferイベントのログを取得します。
	 */
	private function getUnlockPaywallTransferLogs( int $chain_ID, BlockNumberType $from_block, BlockNumberType $to_block ): array {
		return ( new UnlockPaywallTransferCrawler() )->execute( $chain_ID, $from_block, $to_block );
	}

	/**
	 * UnlockPaywallTransferイベントが発生した時のトランザクション情報をDBに保存します。
	 */
	private function saveUnlockPaywallTransaction( \wpdb $wpdb, int $chain_ID, array $unlock_paywall_transfer_logs ): void {
		$transaction_repository = new UnlockPaywallTransaction( $wpdb );

		/** @var string[] */
		$saved_invoice_id_hex_array = array(); // DBに保存済みのinvoiceIDのリスト(DBへのアクセス回数を減らすために使用)

		foreach ( $unlock_paywall_transfer_logs as $unlock_paywall_transfer_log ) {
			$event_args = $this->app_abi->decodeEventParameters( $unlock_paywall_transfer_log );
			assert( is_array( $event_args ), '[80A37466] event_args is not array' );
			/** @var BigInteger */
			$invoice_ID     = $event_args['invoiceID'];
			$invoice_ID_hex = Hex::from( $invoice_ID );

			// 既に保存済みのinvoiceIDの場合はスキップ
			if ( in_array( $invoice_ID_hex, $saved_invoice_id_hex_array ) ) {
				continue;
			} else {
				$saved_invoice_id_hex_array[] = $invoice_ID_hex;
			}

			$transaction_hash = $unlock_paywall_transfer_log->transactionHash;
			$block_number_hex = $unlock_paywall_transfer_log->blockNumber;

			$transaction_repository->save(
				InvoiceID::from( $invoice_ID_hex ),
				$chain_ID,
				BlockNumberType::from( $block_number_hex ),
				$transaction_hash,
			);
		}
	}

	/**
	 * UnlockPaywallTransferイベントのログをDBに保存します。
	 */
	private function saveUnlockPaywallTransfer( \wpdb $wpdb, array $unlock_paywall_transfer_logs ): void {

		$transfer_event_repository = new UnlockPaywallTransferEvent( $wpdb );

		foreach ( $unlock_paywall_transfer_logs as $unlock_paywall_transfer_log ) {
			$event_args = $this->app_abi->decodeEventParameters( $unlock_paywall_transfer_log );
			assert( is_array( $event_args ), '[66C28129] event_args is not array' );

			// イベント発行時の引数を取得
			// /** @var string */
			$from = $event_args['from'];
			// /** @var string */
			$to = $event_args['to'];
			// /** @var string */
			// $token      = $event_args['token'];
			/** @var BigInteger */
			$amount = $event_args['amount'];
			/** @var BigInteger */
			$invoice_ID = $event_args['invoiceID'];

			/** @var string */
			$log_index_hex = $unlock_paywall_transfer_log->logIndex;

			$transfer_event_repository->save(
				InvoiceID::from( Hex::from( $invoice_ID ) ),
				Hex::toInt( $log_index_hex ),
				$from,
				$to,
				Hex::from( $amount ),
			);
		}
	}
}

// --------------------------------------------------------------------------------

/**
 * このサーバーに関係する`UnlockPaywallTransfer`イベントのログを取得するクラス
 */
class UnlockPaywallTransferCrawler {

	public function __construct() {
		// UnlockPaywallTransferイベントのtopic
		$topic_hash = ( new AppAbi() )->topicHash( 'UnlockPaywallTransfer' );

		// サーバーの署名用ウォレットアドレス
		$server_signer_address         = ( new ServerSignerData() )->getAddress();
		$server_signer_address_bytes32 = ( new Padding() )->toBytes32Hex( $server_signer_address ); // topicsは32バイトで記録されているため変換

		$this->topics = array(
			$topic_hash,
			$server_signer_address_bytes32,
		);
	}

	private array $topics;

	/**
	 * このサーバーに関係するUnlockPaywallTransferイベントのログを取得します。
	 *
	 * @return stdClass[]
	 */
	public function execute( int $chain_ID, BlockNumberType $from_block, BlockNumberType $to_block ): array {
		$blockchain_client = ( new BlockchainClientFactory() )->create( $chain_ID );

		$contract_address = ( new AppClientFactory() )->create( $chain_ID )->address();

		/** @var array|null */
		$logs_result = null;
		$blockchain_client->getLogs(
			array(
				'fromBlock' => $from_block->hex(),
				'toBlock'   => $to_block->hex(),
				'address'   => $contract_address,
				'topics'    => $this->topics,
			),
			function ( $err, $logs ) use ( &$logs_result ) {
				if ( $err ) {
					throw $err;
				}
				$logs_result = $logs;
			}
		);
		return $logs_result;
	}
}
