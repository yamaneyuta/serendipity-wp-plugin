<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Factory\ServerSignerServiceFactory;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Calc\SolidityStrings;
use Cornix\Serendipity\Core\Repository\ConsumerTerms;
use wpdb;

/** Invoiceから署名用のメッセージを作成し、署名用ウォレットで署名を行います */
class SingInvoice {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	public function handle( Invoice $invoice ): SingInvoiceResult {

		// 署名用ウォレットで署名を行うためのメッセージを作成
		$server_message = SolidityStrings::valueToHexString( $invoice->chainID()->value() )
			. SolidityStrings::addressToHexString( $invoice->sellerAddress() )
			. SolidityStrings::addressToHexString( $invoice->consumerAddress() )
			. SolidityStrings::valueToHexString( $invoice->id()->hex() )
			. SolidityStrings::valueToHexString( $invoice->postID() )
			. SolidityStrings::addressToHexString( $invoice->paymentTokenAddress() )
			. SolidityStrings::valueToHexString( $invoice->paymentAmountHex() )
			. SolidityStrings::valueToHexString( ( new ConsumerTerms() )->currentVersion() )
			. SolidityStrings::addressToHexString( Ethers::zeroAddress() )    // TODO: アフィリエイターのアドレス
			. SolidityStrings::valueToHexString( 0 );  // TODO: アフィリエイト報酬率

		// サーバーの署名用ウォレットで署名
		$server_signer    = ( new ServerSignerServiceFactory( $this->wpdb ) )->create()->getServerSigner();
		$server_signature = $server_signer->signMessage( $server_message );

		return new SingInvoiceResult( $server_message, $server_signature );
	}
}

class SingInvoiceResult {
	public function __construct( string $message, string $signature ) {
		$this->message   = $message;
		$this->signature = $signature;
	}

	private string $message;
	private string $signature;

	public function message(): string {
		return $this->message;
	}
	public function signature(): string {
		return $this->signature;
	}
}
