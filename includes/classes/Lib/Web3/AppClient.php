<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Security\Judge;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class AppClient {
	public function __construct( string $rpc_url, string $contract_address ) {
		$this->app = ( new ContractFactory() )->create( $rpc_url, ( new AppAbi() )->get(), $contract_address );
		assert( $this->app->getToAddress() === $contract_address, '[A887DAB2] Contract address mismatch' );
	}
	private Contract $app;

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

				$result = new PaywallStatusResult( $is_unlocked, $invoice_ID, $unlocked_block_number );
			}
		);

		assert( ! is_null( $result ) );
		return $result;
	}

	/**
	 * 接続するコントラクトアドレスを取得します。
	 */
	public function address(): string {
		return $this->app->getToAddress();
	}
}

/**
 * @internal
 */
class PaywallStatusResult {
	public function __construct( bool $is_unlocked, BigInteger $invoice_ID, BigInteger $unlocked_block_number ) {
		$this->is_unlocked           = $is_unlocked;
		$this->invoice_ID            = $invoice_ID;
		$this->unlocked_block_number = $unlocked_block_number;
	}
	private bool $is_unlocked;
	private BigInteger $invoice_ID;
	private BigInteger $unlocked_block_number;

	public function isUnlocked(): bool {
		return $this->is_unlocked;
	}

	public function invoiceID(): BigInteger {
		return $this->invoice_ID;
	}

	public function unlockedBlockNumber(): BigInteger {
		return $this->unlocked_block_number;
	}
}
