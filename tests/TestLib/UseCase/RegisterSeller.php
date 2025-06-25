<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestLib\UseCase;

use Cornix\Serendipity\Core\Application\Service\TermsService;
use Cornix\Serendipity\Core\Domain\Entity\Signer;
use Cornix\Serendipity\Core\Domain\Service\WalletService;

/** 指定したユーザーを販売者として登録します */
class RegisterSeller {

	public function __construct( TermsService $terms_service, WalletService $wallet_service ) {
		$this->terms_service  = $terms_service;
		$this->wallet_service = $wallet_service;
	}

	private TermsService $terms_service;
	private WalletService $wallet_service;

	public function handle( Signer $signer ): void {
		$seller_terms = $this->terms_service->getCurrentSellerTerms();
		$signature    = $this->wallet_service->signMessage( $signer, $seller_terms->message() );
		$this->terms_service->saveSellerSignature( $signature );
	}
}
