<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

interface ChainRepository {

	/** 指定したチェーンIDに合致するチェーン情報を取得します。 */
	public function get( ChainID $chain_id ): ?Chain;

	/**
	 * データが存在するチェーン一覧(すべて)を取得します。
	 *
	 * @return Chain[]
	 */
	public function all(): array;

	/** チェーン情報を保存します。 */
	public function save( Chain $chain ): void;
}
