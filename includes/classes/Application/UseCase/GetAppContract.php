<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\AppContractDto;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

/** 指定したチェーンIDのAppコントラクト情報を取得します */
class GetAppContract {

	public function __construct( AppContractRepository $app_contract_repository ) {
		$this->app_contract_repository = $app_contract_repository;
	}

	private AppContractRepository $app_contract_repository;

	public function handle( int $chain_id ): ?AppContractDto {
		$app_contract = $this->app_contract_repository->get( new ChainID( $chain_id ) );
		return null !== $app_contract ? AppContractDto::fromEntity( $app_contract ) : null;
	}
}
