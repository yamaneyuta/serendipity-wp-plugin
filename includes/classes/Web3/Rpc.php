<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Web3;

use Cornix\Serendipity\Core\Logger\Logger;
use Web3\Eth;

class Rpc {

	public static function getChainId( string $rpc_url ): int {
		$eth      = new Eth( $rpc_url );
		$chain_id = -1;

		$eth->chainId(
			function ( $err, $res ) use ( &$chain_id ) {
				if ( $err ) {
					Logger::error( $err );
					throw $err;
				}

				$chain_id = (int) $res->toString();
			}
		);

		if ( -1 === $chain_id ) {
			throw new \Exception( '{A8A0E642-B621-4207-BDB4-A7189D4EA111}' );
		}

		return $chain_id;
	}

	/**
	 * 現在のブロック番号を取得します。
	 *
	 * @param string $rpc_url RPC URL
	 * @return string ブロック番号 (16進数)
	 * TODO: getBlockNumber => getBlockNumberHex
	 */
	public static function getBlockNumber( string $rpc_url ): string {
		$eth              = new Eth( $rpc_url );
		$block_number_hex = null;

		$eth->blockNumber(
			function ( $err, $res ) use ( &$block_number_hex ) {
				if ( $err ) {
					Logger::error( $err );
					throw $err;
				}

				$block_number_hex = '0x' . $res->toHex();
			}
		);

		if ( is_null( $block_number_hex ) ) {
			Logger::error( 'block_number_hex is null' );
			throw new \Exception( '{53CCEA16-91BA-4ABE-820D-EBD18F078311}' );
		}

		return $block_number_hex;
	}
}
