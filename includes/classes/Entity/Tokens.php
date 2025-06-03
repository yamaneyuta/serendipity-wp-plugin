<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Entity\EntityArrayBase;
use Cornix\Serendipity\Core\ValueObject\TableRecord\TokenTableRecord;

final class Tokens extends EntityArrayBase {

	public function __construct( array $tokens ) {
		parent::__construct( $tokens );
	}

	/**
	 * @param Token|Token[] $tokens
	 */
	private static function from( $tokens ): self {
		$tokens = is_array( $tokens ) ? $tokens : array( $tokens );
		foreach ( $tokens as $token ) {
			if ( ! ( $token instanceof Token ) ) {
				throw new \InvalidArgumentException( '[202C2A0C] Only Token instances allowed.' );
			}
		}
		return new self( $tokens );
	}

	/**
	 * @param TokenTableRecord[] $records
	 * @return self
	 */
	public static function fromTableRecords( array $records ): self {
		$tokens = array_map(
			fn( $record ) => Token::fromTableRecord( $record ),
			$records
		);
		return self::from( $tokens );
	}
}
