<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\ValueObject\TableRecord\ChainTableRecord;

class Chain {
	/**
	 *
	 * @param int         $chain_id
	 * @param string      $name
	 * @param null|string $rpc_url
	 * @param int|string  $confirmations
	 */
	private function __construct( int $chain_id, string $name, ?string $rpc_url, $confirmations ) {
		$this->id            = $chain_id;
		$this->name          = $name;
		$this->rpc_url       = $rpc_url;
		$this->confirmations = $confirmations;
	}

	public int $id;
	public string $name;
	public ?string $rpc_url;
	/** @var int|string */
	public $confirmations;

	public static function fromTableRecord( ChainTableRecord $record ): self {
		return new self(
			$record->chainID(),
			$record->name(),
			$record->rpcURL(),
			is_numeric( $record->confirmations() ) ? (int) $record->confirmations() : $record->confirmations(),
		);
	}

	/** このチェーンに接続可能かどうかを取得します。 */
	public function connectable(): bool {
		// RPC URLが設定されている場合は接続可能とする
		return ! is_null( $this->rpc_url ) && Validate::isUrl( $this->rpc_url );
	}

	public function networkCategory(): NetworkCategory {
		return NetworkCategory::from( Config::NETWORK_CATEGORIES[ $this->id ] );
	}
}
