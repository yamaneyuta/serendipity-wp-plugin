<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

use stdClass;

class ChainTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	public int $chain_id;
	public string $name;
	public ?string $rpc_url;
	public string $confirmations; // テーブル定義はvarcharなのでstring型で定義する
}
