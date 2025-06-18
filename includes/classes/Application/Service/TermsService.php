<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Repository\SellerTermsRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\SignedTerms;
use Cornix\Serendipity\Core\Domain\ValueObject\Terms;

class TermsService {

	public function __construct( SellerTermsRepository $seller_terms_repository ) {
		$this->seller_terms_repository = $seller_terms_repository ?? new SellerTermsRepository();
	}

	private SellerTermsRepository $seller_terms_repository;

	/** 現在の販売者向け利用規約情報を取得します */
	public function getCurrentSellerTerms(): Terms {
		return $this->seller_terms_repository->currentTerms();
	}

	/** 販売者が同意した利用規約情報を取得します */
	public function getSignedSellerTerms(): ?SignedTerms {
		return $this->seller_terms_repository->get();
	}

	/** 販売者が同意した利用規約情報を保存します */
	public function saveSellerSignature( Signature $signature ): void {
		$terms = $this->seller_terms_repository->currentTerms();    // 登録時は最新の利用規約情報に署名している必要がある
		$this->seller_terms_repository->save( new SignedTerms( $terms, $signature ) );
	}
}
