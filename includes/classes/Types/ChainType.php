<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Repository\RpcURL;

/**
 * チェーンを表すクラス
 */
class ChainType {
	private function __construct( int $chain_ID ) {
		$this->chain_ID = $chain_ID;
	}
	private int $chain_ID;

	public static function from( int $chain_ID ): ChainType {
		return new ChainType( $chain_ID );
	}

	/**
	 * チェーンIDを取得します。
	 */
	public function id(): int {
		return $this->chain_ID;
	}

	/**
	 * サーバーからこのチェーンに接続することが可能かどうかを取得します。
	 */
	public function isConnectable(): bool {
		return ! is_null( ( new RpcURL() )->get( $this->chain_ID ) );
	}
}
