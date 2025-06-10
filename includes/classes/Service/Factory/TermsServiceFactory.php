<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service\Factory;

use Cornix\Serendipity\Core\Repository\SellerTermsRepository;
use Cornix\Serendipity\Core\Service\TermsService;

class TermsServiceFactory {
	public function create(): TermsService {
		$seller_terms_repository = new SellerTermsRepository();
		return new TermsService( $seller_terms_repository );
	}
}
