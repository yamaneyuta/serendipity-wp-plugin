<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Entity\AppContract;
use Cornix\Serendipity\Core\Infrastructure\Web3\AppAbi;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\ContractFactory;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\Web3\PaywallStatusResult;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class AppContractClient {
	public function __construct( AppContract $app_contract ) {
		$rpc_url = $app_contract->chain->rpc_url;
		$address = $app_contract->address;
		// このインスタンスを生成する前に接続可能かどうかをチェックしてください。
		assert( is_string( $rpc_url ) && Judge::isUrl( $rpc_url ), '[A5ED369D] rpc_url: ' . var_export( $rpc_url, true ) );
		$this->app          = ( new ContractFactory() )->create( $rpc_url, ( new AppAbi() )->get(), $address );
		$this->app_contract = $app_contract;
	}
	private Contract $app;
	private AppContract $app_contract;

	public function getPaywallStatus( string $signer_address_hex, int $post_ID, string $consumer_address_hex ): PaywallStatusResult {
		Judge::checkAddress( $signer_address_hex );
		// Judge::checkPostID( $post_ID );
		Judge::checkAddress( $consumer_address_hex );

		/** @var PaywallStatusResult|null */
		$result = null;
		$this->app->call(
			'getPaywallStatus',
			$signer_address_hex,
			$post_ID,
			$consumer_address_hex,
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

				$result = new PaywallStatusResult( $is_unlocked, InvoiceID::from( $invoice_ID ), BlockNumber::from( $unlocked_block_number ) );
			}
		);

		assert( ! is_null( $result ) );
		return $result;
	}

	/**
	 * 接続するコントラクトアドレスを取得します。
	 */
	public function address(): string {
		return $this->app_contract->address;
	}
}
