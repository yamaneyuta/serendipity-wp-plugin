<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Repository\SellerTermsRepository;

class SellerService {

	public function __construct( SellerTermsRepository $seller_terms_repository, WalletService $wallet_service ) {
		$this->seller_terms_repository = $seller_terms_repository;
		$this->wallet_service          = $wallet_service;
	}

	private SellerTermsRepository $seller_terms_repository;
	private WalletService $wallet_service;

	/** 販売者のウォレットアドレスを取得します */
	public function getSellerAddress(): Address {
		// 販売者の署名及びメッセージを取得
		$seller_signature_data = $this->seller_terms_repository->get();
		$message               = $seller_signature_data->terms()->message();
		$signature             = $seller_signature_data->signature();

		// 署名からアドレスを復元して返す
		return $this->wallet_service->recoverAddress( $message, $signature );
	}
}
