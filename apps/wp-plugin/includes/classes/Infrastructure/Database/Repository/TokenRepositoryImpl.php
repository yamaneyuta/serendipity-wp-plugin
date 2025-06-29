<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\TokenTable;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class TokenRepositoryImpl implements TokenRepository {

	public function __construct( ?TokenTable $token_table = null ) {
		$this->token_table = $token_table ?? new TokenTable( $GLOBALS['wpdb'] );
	}

	private TokenTable $token_table;


	/** @inheritdoc */
	public function save( Token $token ): void {
		$this->token_table->save( $token );
	}

	/** @inheritdoc */
	public function all(): array {
		$token_records = $this->token_table->all();
		return array_map(
			fn( $record ) => Token::fromTableRecord( $record ),
			$token_records
		);
	}

	/** @inheritdoc */
	public function get( ChainID $chain_ID, Address $address ): ?Token {
		$tokens = array_filter(
			$this->all(),
			fn( Token $token ) => $token->chainID()->equals( $chain_ID ) && $token->address()->equals( $address )
		);
		assert( is_array( $tokens ) && count( $tokens ) <= 1, '[A236DEBB] Expected at most one token for the given chain ID and address.' );

		return array_values( $tokens )[0] ?? null;
	}
}
