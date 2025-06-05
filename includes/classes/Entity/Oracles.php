<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Entity\EntityArrayBase;
use Cornix\Serendipity\Core\ValueObject\TableRecord\OracleTableRecord;

final class Oracles extends EntityArrayBase {

	/**
	 * @param Oracle[] $oracles
	 */
	public function __construct( array $oracles ) {
		parent::__construct( $oracles );
	}

	/**
	 * @param Oracle|Oracle[] $oracles
	 */
	private static function from( $oracles ): self {
		$oracles = is_array( $oracles ) ? $oracles : array( $oracles );
		foreach ( $oracles as $oracle ) {
			if ( ! ( $oracle instanceof Oracle ) ) {
				throw new \InvalidArgumentException( '[8EA1CBE2] Only Oracle instances allowed.' );
			}
		}
		return new self( $oracles );
	}

	/**
	 * @param OracleTableRecord[] $records
	 * @return self
	 */
	public static function fromTableRecords( array $records ): self {
		$tokens = array_map(
			fn( $record ) => Oracle::fromTableRecord( $record ),
			$records
		);
		return self::from( $tokens );
	}
}
