<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\ValueObject;

use stdClass;

class ChainTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected int $chain_id;
	protected string $name;
	protected int $network_category_id;
	protected ?string $rpc_url;
	protected string $confirmations; // テーブル定義はvarcharなのでstring型で定義する
	protected ?string $block_explorer_url;

	public function chainIdValue(): int {
		return $this->chain_id;
	}

	public function nameValue(): string {
		return $this->name;
	}

	public function networkCategoryIdValue(): int {
		return $this->network_category_id;
	}

	public function rpcUrlValue(): ?string {
		return $this->rpc_url;
	}

	public function confirmationsValue(): string {
		return $this->confirmations;
	}

	public function blockExplorerUrlValue(): ?string {
		return $this->block_explorer_url;
	}
}
