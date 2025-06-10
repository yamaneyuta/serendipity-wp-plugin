<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Entity\AppContract;
use Cornix\Serendipity\Core\Infrastructure\Web3\AppContractAbi;
use Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject\GetPaywallStatusResult;
use Cornix\Serendipity\Core\Lib\Web3\ContractFactory;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class AppContractClient {
	public function __construct( AppContract $app_contract, ?AppContractAbi $app_contract_abi = null ) {
		assert( $app_contract->chain()->connectable(), '[A5ED369D]' );   // 接続可能なチェーンであること

		$address = $app_contract->address();
		// このインスタンスを生成する前に接続可能かどうかをチェックしてください。
		$app_contract_abi   = $app_contract_abi ?? new AppContractAbi();
		$this->contract     = ( new ContractFactory() )->create( $app_contract->chain()->rpcURL(), $app_contract_abi->get(), $address );
		$this->app_contract = $app_contract;
	}
	private Contract $contract;
	private AppContract $app_contract;

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

	/**
	 * 接続するコントラクトアドレスを取得します。
	 */
	public function address(): Address {
		return $this->app_contract->address();
	}
}
