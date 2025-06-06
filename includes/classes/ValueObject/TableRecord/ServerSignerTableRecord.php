<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

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

	public function address(): string {
		return $this->address;
	}
	public function privateKeyData(): string {
		return $this->private_key_data;
	}
	public function encryptionKey(): ?string {
		return $this->encryption_key;
	}
	public function encryptionIv(): ?string {
		return $this->encryption_iv;
	}
}
