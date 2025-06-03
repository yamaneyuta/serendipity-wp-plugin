<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Entity\Token;
use Cornix\Serendipity\Core\Entity\Tokens;
use Cornix\Serendipity\Core\Repository\TableGateway\TokenTable;
use Cornix\Serendipity\Core\ValueObject\Address;

class TokenRepository {

	public function __construct( ?TokenTable $token_table = null ) {
		$this->token_table = $token_table ?? new TokenTable( $GLOBALS['wpdb'] );
	}

	private TokenTable $token_table;


	/**
	 * トークンデータを保存します。
	 */
	public function save( Token $token ): void {
		$this->token_table->save( $token );
	}

	/**
	 * トークンデータ一覧を取得します。
	 */
	public function all(): Tokens {
		$token_records = $this->token_table->all();
		return Tokens::fromTableRecords( $token_records );
	}

	/**
	 * 指定したチェーンID、アドレスに一致するトークン情報を取得します。
	 */
	public function get( int $chain_ID, Address $address ): ?Token {
		$tokens = $this->all();

		return $tokens->find(
			fn( Token $token ) => $token->chainID() === $chain_ID && $token->address()->equals( $address )
		);
	}
}
