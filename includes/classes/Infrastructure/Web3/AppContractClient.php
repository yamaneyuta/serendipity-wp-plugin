<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Infrastructure\Web3\AppContractAbi;
use Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject\GetPaywallStatusResult;
use Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject\UnlockPaywallTransferEvent;
use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;
use Cornix\Serendipity\Core\Infrastructure\Web3\ContractFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\TransactionHash;
use Cornix\Serendipity\Core\Domain\ValueObject\UnlockPaywallTransferType;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class AppContractClient {

	private const EVENT_NAME_UNLOCK_PAYWALL_TRANSFER = 'UnlockPaywallTransfer';

	public function __construct( AppContract $app_contract, ?AppContractAbi $app_contract_abi = null ) {
		assert( $app_contract->chain()->connectable(), '[A5ED369D]' );   // 接続可能なチェーンであること

		$this->app_contract      = $app_contract;
		$this->abi               = $app_contract_abi ?? new AppContractAbi();
		$this->contract          = ( new ContractFactory() )->create(
			$app_contract->chain()->rpcURL(),
			$this->abi->get(),
			$app_contract->address()
		);
		$this->blockchain_client = new BlockchainClient( $app_contract->chain()->rpcURL() );
	}
	private Contract $contract;
	private AppContractAbi $abi;
	private AppContract $app_contract;
	private BlockchainClient $blockchain_client;

	protected function contract(): Contract {
		return $this->contract;
	}

	public function getPaywallStatus( Address $signer_address, int $post_ID, Address $consumer_address ): GetPaywallStatusResult {

		/** @var GetPaywallStatusResult|null */
		$result = null;
		$this->contract->call(
			'getPaywallStatus',
			$signer_address->value(),
			$post_ID,
			$consumer_address->value(),
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					throw $err;
				}

				$is_unlocked           = $res['isUnlocked'];
				$invoice_ID            = $res['invoiceID'];
				$unlocked_block_number = $res['unlockedBlockNumber'];

				assert( is_bool( $is_unlocked ) );
				assert( $invoice_ID instanceof BigInteger );
				assert( $unlocked_block_number instanceof BigInteger );

				$result = new GetPaywallStatusResult( $is_unlocked, InvoiceID::from( $invoice_ID ), BlockNumber::from( $unlocked_block_number ) );
			}
		);

		assert( ! is_null( $result ) );
		return $result;
	}

	public function getUnlockPaywallTransferEvents(
		BlockNumber $from_block,
		BlockNumber $to_block,
		Address $server_signer_address
	) {
		assert( $from_block->compare( $to_block ) <= 0, '[438F5DEE] from_block must be less than or equal to to_block.' );

		$filter = array(
			'fromBlock' => $from_block->hex(),
			'toBlock'   => $to_block->hex(),
			'address'   => $this->app_contract->address()->value(),
			'topics'    => array(
				( new AppContractAbi() )->topicHash( self::EVENT_NAME_UNLOCK_PAYWALL_TRANSFER ),
				$server_signer_address->toBytes32Hex(),
			),
		);

		/** @var UnlockPaywallTransferEvent[] */
		$results = array();
		$this->blockchain_client->getLogs(
			$filter,
			function ( $err, $logs ) use ( &$results ) {
				if ( $err ) {
					throw $err;
				}

				$results = array();
				foreach ( $logs as $log ) {
					$decoded_event_parameters = $this->abi->decodeEventParameters( $log );
					$results[]                = new UnlockPaywallTransferEvent(
						BlockNumber::from( $log->blockNumber ), // block_number
						hexdec( $log->logIndex ), // log_index
						TransactionHash::from( $log->transactionHash ), // transaction_hash
						InvoiceID::from( $decoded_event_parameters['invoiceID'] ), // invoice_id
						Address::from( $decoded_event_parameters['signer'] ), // server_signer_address
						Address::from( $decoded_event_parameters['from'] ), // from_address
						Address::from( $decoded_event_parameters['to'] ), // to_address
						Address::from( $decoded_event_parameters['token'] ), // token_address
						Hex::from( $decoded_event_parameters['amount'] ), // amount_hex
						UnlockPaywallTransferType::from( (int) ( $decoded_event_parameters['transferType'] )->toString() ) // transfer_type
					);
				}
			}
		);

		return $results;
	}
}
