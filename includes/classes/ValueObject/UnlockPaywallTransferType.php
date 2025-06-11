<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use Cornix\Serendipity\Core\Constant\UnlockPaywallTransferTypeID;

class UnlockPaywallTransferType {

	private function __construct( int $unlock_paywall_transfer_type_id ) {
		self::checkValidTransferTypeID( $unlock_paywall_transfer_type_id );
		$this->id = $unlock_paywall_transfer_type_id;
	}

	private int $id;

	public function id(): int {
		return $this->id;
	}

	public static function from( int $unlock_paywall_transfer_type_id ): self {
		return new self( $unlock_paywall_transfer_type_id );
	}

	private static function checkValidTransferTypeID( int $unlock_paywall_transfer_type_id ): void {
		if ( ! in_array( $unlock_paywall_transfer_type_id, self::allTransferTypeIDs(), true ) ) {
			throw new \InvalidArgumentException( '[F468C1FA] Invalid unlock paywall transfer type ID: ' . $unlock_paywall_transfer_type_id );
		}
	}

	private function allTransferTypeIDs(): array {
		$reflection = new \ReflectionClass( UnlockPaywallTransferTypeID::class );
		$constants  = $reflection->getConstants();
		/** @var int[] */
		$all_transfer_type_ids = array_values( $constants );
		return $all_transfer_type_ids;
	}
}
