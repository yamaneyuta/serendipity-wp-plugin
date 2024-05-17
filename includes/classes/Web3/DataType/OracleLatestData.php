<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Web3\DataType;

/**
 * getOracleLatestDataの戻り値の型
 */
class OracleLatestData {

	/** @var string */
	public $symbol;

	/** @var string */
	public $address;

	/** @var string */
	public $round_id_hex;

	/** @var string */
	public $answer_hex;

	/** @var int */
	public $updated_at;

	/** @var int */
	public $decimals;

	/** @var string */
	public $description;

	/** @var string */
	public $version_hex;

	public function __construct(
		string $symbol,
		string $address,
		string $round_id_hex,
		string $answer_hex,
		int $updated_at,
		int $decimals,
		string $description,
		string $version_hex
	) {
		$this->symbol       = $symbol;
		$this->address      = $address;
		$this->round_id_hex = $round_id_hex;
		$this->answer_hex   = $answer_hex;
		$this->updated_at   = $updated_at;
		$this->decimals     = $decimals;
		$this->description  = $description;
		$this->version_hex  = $version_hex;
	}
}
