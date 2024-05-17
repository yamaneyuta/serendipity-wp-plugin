<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Database\DataType;

class LogData {

	/** @var float */
	public $log_timestamp;
	/** @var string */
	public $log_level;
	/** @var string */
	public $uri;
	/** @var string */
	public $source;
	/** @var string */
	public $log_message;
	/** @var string */
	public $plugin_version;

	public function __construct(
		float $log_timestamp,
		string $log_level,
		string $uri,
		string $source,
		string $log_message,
		string $plugin_version
	) {
		$this->log_timestamp  = $log_timestamp;
		$this->log_level      = $log_level;
		$this->uri            = $uri;
		$this->source         = $source;
		$this->log_message    = $log_message;
		$this->plugin_version = $plugin_version;
	}
}
