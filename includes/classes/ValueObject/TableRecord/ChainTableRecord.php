<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

use stdClass;

class ChainTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected int $chain_id;
	protected string $name;
	protected ?string $rpc_url;
	protected string $confirmations; // テーブル定義はvarcharなのでstring型で定義する

	public function chainID(): int {
		return $this->chain_id;
	}

	public function name(): string {
		return $this->name;
	}

	public function rpcURL(): ?string {
		return $this->rpc_url;
	}

	public function confirmations(): string {
		return $this->confirmations;
	}
}
