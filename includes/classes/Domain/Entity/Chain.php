<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ChainTableRecord;

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

	private int $id;
	private string $name;
	private ?string $rpc_url;
	/** @var int|string */
	private $confirmations;

	public static function fromTableRecord( ChainTableRecord $record ): self {
		/** @var string $confirmations */
		$confirmations = $record->confirmations();
		return new self(
			$record->chainID(),
			$record->name(),
			$record->rpcURL(),
			is_numeric( $confirmations ) ? (int) $confirmations : $confirmations,
		);
	}

	public function id(): int {
		return $this->id;
	}
	public function name(): string {
		return $this->name;
	}
	public function rpcURL(): ?string {
		return $this->rpc_url;
	}
	public function setRpcURL( ?string $rpc_url ): void {
		$this->rpc_url = $rpc_url;
	}
	/**
	 * @return int|string
	 */
	public function confirmations() {
		return $this->confirmations;
	}
	/**
	 * このチェーンの待機ブロック数を設定します
	 *
	 * @param int|string $confirmations
	 */
	public function setConfirmations( $confirmations ): void {
		if ( ! is_int( $confirmations ) && ! is_string( $confirmations ) ) {
			throw new \InvalidArgumentException( '[E8113094] Confirmations must be an integer or a string representing an integer.' );
		}
		$this->confirmations = $confirmations;
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
