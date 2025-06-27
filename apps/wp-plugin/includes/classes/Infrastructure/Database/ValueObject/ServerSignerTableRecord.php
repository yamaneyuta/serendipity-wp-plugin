<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\ValueObject;

use stdClass;

class ServerSignerTableRecord extends TableRecordBase {
	/** @disregard P1009 Undefined type */
	public function __construct(
		#[\SensitiveParameter]
		stdClass $record
	) {
		$this->import( $record );
	}

	protected string $address;
	protected string $private_key_data;
	protected ?string $encryption_key;
	protected ?string $encryption_iv;

	public function addressValue(): string {
		return $this->address;
	}
	public function privateKeyDataValue(): string {
		return $this->private_key_data;
	}
	public function encryptionKeyValue(): ?string {
		return $this->encryption_key;
	}
	public function encryptionIvValue(): ?string {
		return $this->encryption_iv;
	}
}
