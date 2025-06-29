<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

interface TokenRepository {

	/** トークンデータを保存します。 */
	public function save( Token $token ): void;

	/**
	 * トークンデータ一覧を取得します。
	 *
	 * @return Token[]
	 */
	public function all(): array;

	/** 指定したチェーンID、アドレスに一致するトークン情報を取得します。 */
	public function get( ChainID $chain_ID, Address $address ): ?Token;
}
