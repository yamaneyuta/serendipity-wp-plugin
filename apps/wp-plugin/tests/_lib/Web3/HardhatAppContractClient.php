<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Infrastructure\Factory\AppContractRepositoryFactory;
use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Domain\Entity\Signer;
use Cornix\Serendipity\Core\Infrastructure\Web3\AppContractAbi;
use Cornix\Serendipity\Core\Infrastructure\Web3\AppContractClient;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\ChainRepositoryImpl;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use phpseclib\Math\BigInteger;

class HardhatAppContractClient extends AppContractClient {
	private function __construct( AppContract $app_contract, AppContractAbi $app_abi ) {
		parent::__construct( $app_contract, $app_abi );
	}

	public static function fromChainID( ChainID $chain_ID ): self {
		assert( ( new ChainRepositoryImpl( new ChainTable( $GLOBALS['wpdb'] ) ) )->get( $chain_ID )->networkCategoryID()->equals( NetworkCategoryID::privatenet() ) );
		$app_contract = ( new AppContractRepositoryFactory() )->create()->get( $chain_ID );
		$app_abi      = new HardhatAppContractABI();
		return new self( $app_contract, $app_abi );
	}

	public function unlockPaywall(
		Signer $from,
		Signature $server_signature,
		string $seller_terms_message_hash,
		Signature $seller_terms_signature,
		int $consumer_terms_version,
		string $affiliate_terms_message_hash,
		string $affiliate_terms_signature,
		InvoiceID $invoice_id,
		int $post_id,
		Address $payment_token_address,
		Amount $payment_amount,
		int $affiliate_ratio
	): ?string {
		/** @var null|string */
		$result = null;
		$this->contract()->send(
			'unlockPaywall',
			$server_signature->value(),
			$seller_terms_message_hash,
			$seller_terms_signature->value(),
			$consumer_terms_version,
			$affiliate_terms_message_hash,
			$affiliate_terms_signature,
			$invoice_id->hex(),
			$post_id,
			$payment_token_address->value(),
			$payment_amount,
			$affiliate_ratio,
			array(
				'from'  => $from->address()->value(),
				'value' => HexFormat::toHex( new BigInteger( $payment_amount->value() ) ), // 支払い金額を指定
			),
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					error_log( '[Error] An error occurred: ' . $err->getMessage() );
					throw $err;
				} elseif ( ! is_string( $res ) ) {
					throw new \UnexpectedValueException( '[E4A5CE0F] Expected transaction hash to be a string. ' . gettype( $res ) );
				}
				$result = $res;
			}
		);

		return $result;
	}
}

class HardhatAppContractABI extends AppContractAbi {
	/** @inheritdoc */
	public function get(): array {
		$abi_json  = <<<JSON
		{
			"abi": [
				{
					"inputs": [
						{
							"internalType": "bytes",
							"name": "serverSignature",
							"type": "bytes"
						},
						{
							"internalType": "bytes32",
							"name": "sellerTermsMessageHash",
							"type": "bytes32"
						},
						{
							"internalType": "bytes",
							"name": "sellerTermsSignature",
							"type": "bytes"
						},
						{
							"internalType": "uint256",
							"name": "consumerTermsVersion",
							"type": "uint256"
						},
						{
							"internalType": "bytes32",
							"name": "affiliateTermsMessageHash",
							"type": "bytes32"
						},
						{
							"internalType": "bytes",
							"name": "affiliateTermsSignature",
							"type": "bytes"
						},
						{
							"internalType": "uint128",
							"name": "invoiceID",
							"type": "uint128"
						},
						{
							"internalType": "uint64",
							"name": "postID",
							"type": "uint64"
						},
						{
							"internalType": "address",
							"name": "paymentToken",
							"type": "address"
						},
						{
							"internalType": "uint256",
							"name": "paymentAmount",
							"type": "uint256"
						},
						{
							"internalType": "uint256",
							"name": "affiliateRatio",
							"type": "uint256"
						}
					],
					"name": "unlockPaywall",
					"outputs": [],
					"stateMutability": "payable",
					"type": "function"
				}
			]
		}
		JSON;
		$extra_abi = json_decode( $abi_json, true )['abi'];

		// 親クラスのABIとマージして返す
		$parent_abi = parent::get();
		assert( is_array( $parent_abi ) );
		return array_merge( $parent_abi, $extra_abi );
	}
}
