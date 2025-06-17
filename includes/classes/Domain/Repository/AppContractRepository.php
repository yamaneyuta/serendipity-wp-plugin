<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

interface AppContractRepository {

	/** Appコントラクトの情報を取得します */
	public function get( ChainID $chain_id ): ?AppContract;

	/** Appコントラクトの情報を保存します */
	public function save( AppContract $app_contract ): void;
}
