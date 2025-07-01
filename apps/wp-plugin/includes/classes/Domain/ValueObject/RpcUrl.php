<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Lib\Security\Validate;

/**
 * RPC URLを表すValueObjectクラス
 */
final class RpcUrl {

	public function __construct( string $rpc_url_value ) {
		self::checkValidRpcUrlFormat( $rpc_url_value );
		$this->rpc_url_value = $rpc_url_value;
	}
	private string $rpc_url_value;

	public static function from( ?string $rpc_url_value ): ?self {
		return is_null( $rpc_url_value ) ? null : new self( $rpc_url_value );
	}

	public function value(): string {
		return $this->rpc_url_value;
	}

	public function __toString(): string {
		return $this->rpc_url_value;
	}

	public function equals( RpcUrl $other ): bool {
		return $this->rpc_url_value === $other->rpc_url_value;
	}

	private static function checkValidRpcUrlFormat( string $rpc_url_value ): void {
		if ( ! Validate::isUrl( $rpc_url_value ) ) {
			throw new \InvalidArgumentException( '[A8D0FAC8] Invalid RPC URL format. ' . $rpc_url_value );
		}

		// RPC URLはHTTPまたはHTTPSである必要がある
		if ( ! ( str_starts_with( $rpc_url_value, 'http://' ) || str_starts_with( $rpc_url_value, 'https://' ) ) ) {
			throw new \InvalidArgumentException( '[81E9BE6D] RPC URL must be HTTP or HTTPS. ' . $rpc_url_value );
		}
	}
}
