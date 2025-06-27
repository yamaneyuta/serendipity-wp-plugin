<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;

interface OracleRepository {
	/**
	 * Oracle情報をすべて取得します。
	 *
	 * @return Oracle[]
	 */
	public function all(): array;
}
