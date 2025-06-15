<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\ChainID;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;

class Chain {
	/**
	 *
	 * @param ChainID         $chain_id
	 * @param string          $name
	 * @param NetworkCategory $network_category
	 * @param null|string     $rpc_url
	 * @param int|string      $confirmations
	 */
	protected function __construct( ChainID $chain_id, string $name, NetworkCategory $network_category, ?string $rpc_url, $confirmations ) {
		$this->id               = $chain_id;
		$this->name             = $name;
		$this->network_category = $network_category;
		$this->rpc_url          = $rpc_url;
		$this->confirmations    = $confirmations;
	}

	private ChainID $id;
	private string $name;
	private NetworkCategory $network_category;
	private ?string $rpc_url;
	/** @var int|string */
	private $confirmations;

	public function id(): ChainID {
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
		return $this->network_category;
	}
}
