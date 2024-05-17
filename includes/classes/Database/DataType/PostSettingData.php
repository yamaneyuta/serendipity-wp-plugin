<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Database\DataType;

class PostSettingData {
	/** @var string */
	public $hist_set_post_id;
	/** @var int */
	public $post_id;
	/** @var bool */
	public $selling_paused;
	/** @var string */
	public $selling_amount_hex;
	/** @var int */
	public $selling_decimals;
	/** @var string */
	public $selling_symbol;
	/** @var string */
	public $affiliate_percent_amount_hex;
	/** @var int */
	public $affiliate_percent_decimals;

	public function __construct(
		string $hist_set_post_id,
		int $post_id,
		bool $selling_paused,
		string $selling_amount_hex,
		int $selling_decimals,
		string $selling_symbol,
		string $affiliate_percent_amount_hex,
		int $affiliate_percent_decimals
	) {
		$this->hist_set_post_id             = $hist_set_post_id;
		$this->post_id                      = $post_id;
		$this->selling_paused               = $selling_paused;
		$this->selling_amount_hex           = $selling_amount_hex;
		$this->selling_decimals             = $selling_decimals;
		$this->selling_symbol               = $selling_symbol;
		$this->affiliate_percent_amount_hex = $affiliate_percent_amount_hex;
		$this->affiliate_percent_decimals   = $affiliate_percent_decimals;
	}
}
