<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\AnkrRpcUrlDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\PolygonLabsRpcUrlDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\PrivatenetRpcUrlDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\PublicNodeRpcUrlDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\RpcUrlDefinitionBase;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\RpcComRpcUrlDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\SoneiumRpcUrlDefinition;

class RpcURL {

	public function __construct() {
		// 仮で組み込みのRPC URLを登録
		// TODO: ユーザーが利用規約に同意したかどうかの情報を取得
		$third_party_rpc_url_defs   = array();
		$third_party_rpc_url_defs[] = new BuiltInRpcURL( new SoneiumRpcUrlDefinition(), true );
		$third_party_rpc_url_defs[] = new BuiltInRpcURL( new PolygonLabsRpcUrlDefinition(), true );
		$third_party_rpc_url_defs[] = new BuiltInRpcURL( new AnkrRpcUrlDefinition(), true );
		$third_party_rpc_url_defs[] = new BuiltInRpcURL( new RpcComRpcUrlDefinition(), true );
		$third_party_rpc_url_defs[] = new BuiltInRpcURL( new PublicNodeRpcUrlDefinition(), true );

		// プライベートネット用のRPC URL
		$privatenet_rpc_url_defs   = array();
		$privatenet_rpc_url_defs[] = new class() implements IRpcURL {
			public function connectableURL( int $chain_ID ): ?string {
				return ( new PrivatenetRpcUrlDefinition() )->get( $chain_ID );
			}
		};

		// すべてのRPC URL定義をマージ
		$this->all_rpc_url_defs = array_merge(
			$third_party_rpc_url_defs,  // 組み込みのRPC URL
			$privatenet_rpc_url_defs,   // プライベートネット接続用のRPC URL
		);
	}
	private array $all_rpc_url_defs = array();

	/**
	 * 指定したチェーンに接続できるRPC URL一覧を取得します。
	 *
	 * @param int $chain_ID
	 * @return string[]
	 */
	public function allConnectableURL( int $chain_ID ): array {
		$result = array();
		foreach ( $this->all_rpc_url_defs as $rpc_url_def ) {
			$url = $rpc_url_def->connectableURL( $chain_ID );
			if ( ! is_null( $url ) ) {
				$result[] = $url;
			}
		}
		return $result;
	}

	/**
	 * 指定したチェーンに接続できるRPC URLを取得します。
	 */
	public function connectableURL( int $chain_ID ): ?string {
		foreach ( $this->all_rpc_url_defs as $rpc_url_def ) {
			$url = $rpc_url_def->connectableURL( $chain_ID );
			if ( ! is_null( $url ) ) {
				return $url;
			}
		}
		return null;
	}

	/**
	 * 指定したチェーンに接続できるかどうかを取得します。
	 */
	public function isConnectable( int $chain_ID ): bool {
		return ! is_null( $this->connectableURL( $chain_ID ) );
	}
}

interface IRpcURL {
	/**
	 * 指定したチェーンに接続できるRPC URLを取得します。
	 */
	public function connectableURL( int $chain_ID ): ?string;
}

/**
 * ユーザーが設定したRPC URLに関する情報を取得するクラス
 *
 * @internal
 */
class UserSettingsRpcURL implements IRpcURL {
	public function connectableURL( int $chain_ID ): ?string {
		// TODO: 実装
		throw new \Exception( '[A3A82122] UserSettingsRpcURL::connectableURL() - Not implemented' );
	}
}


/**
 * このプラグインで定義している組み込みのRPC URLに関する情報を取得するクラス
 *
 * @internal
 */
class BuiltInRpcURL implements IRpcURL {
	public function __construct( RpcUrlDefinitionBase $definition, bool $is_terms_agreed ) {
		$this->definition      = $definition;
		$this->is_terms_agreed = $is_terms_agreed;
	}
	private RpcUrlDefinitionBase $definition;
	private bool $is_terms_agreed;

	public function connectableURL( int $chain_ID ): ?string {
		// RPC URLを取得
		// ※ 利用規約に同意していない場合はnullを返す
		return $this->is_terms_agreed ? $this->definition->get( $chain_ID ) : null;
	}
}
