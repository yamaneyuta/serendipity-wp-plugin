<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\RpcUrlProviderType;

/**
 * RPCに関連するユーザー設定
 */
class RpcUserSettings {
	public function __construct() {
		$this->rpc_url_user_settings     = new RpcUrlUserSettings();
		$this->agreed_rpc_provider_terms = new AgreedRpcProviderTerms();
	}

	private RpcUrlUserSettings $rpc_url_user_settings;
	private AgreedRpcProviderTerms $agreed_rpc_provider_terms;

	/**
	 * 指定されたチェーンIDに対応する、ユーザーが設定したRPC URLを取得します。
	 *
	 * @param int $chain_ID
	 */
	public function getRpcURL( int $chain_ID ): ?string {
		return $this->rpc_url_user_settings->get( $chain_ID );
	}

	/**
	 * 指定されたチェーンIDに対応する、ユーザーが設定したRPC URLを設定します。
	 * RPC URLを削除する場合はnullを指定します。
	 *
	 * @param int         $chain_ID
	 * @param string|null $rpc_url
	 */
	public function setRpcURL( int $chain_ID, ?string $rpc_url ): void {
		$this->rpc_url_user_settings->set( $chain_ID, $rpc_url );
	}

	/**
	 * ユーザーが設定したRPC URLを使用する設定かどうかを取得します。
	 *
	 * @param int $chain_ID
	 */
	public function isUseCustomRpcUrl( int $chain_ID ): bool {
		// ユーザーが設定したRPC URLがある場合はtrueを返す
		return ! is_null( $this->getRpcURL( $chain_ID ) );
	}

	/**
	 * 指定されたRPC URL提供者の利用規約に同意したかどうかを取得します。
	 *
	 * @param RpcUrlProviderType $rpc_url_provider
	 */
	public function getIsAgreedTerms( RpcUrlProviderType $rpc_url_provider ): bool {
		return $this->agreed_rpc_provider_terms->get( $rpc_url_provider );
	}
}


/**
 * ユーザーが管理画面で設定したRPC URLを取得または設定するクラス
 *
 * @internal
 */
class RpcUrlUserSettings {
	/**
	 * 指定したチェーンのRPC URLを取得します。
	 *
	 * @param int $chain_ID
	 * @return string|null
	 */
	public function get( int $chain_ID ): ?string {
		$rpc_url = ( new OptionFactory() )->rpcURL( $chain_ID )->get();
		assert( is_null( $rpc_url ) || Judge::isUrl( $rpc_url ), '[523BCB32] Invalid RPC URL. - rpc_url: ' . var_export( $rpc_url, true ) );
		return $rpc_url;
	}

	/**
	 * 指定したチェーンのRPC URLを設定します。
	 * RPC URLを削除する場合はnullを指定します。
	 *
	 * @param int         $chain_ID
	 * @param string|null $rpc_url
	 */
	public function set( int $chain_ID, ?string $rpc_url ): void {
		$rpc_option = ( new OptionFactory() )->rpcURL( $chain_ID );
		if ( is_null( $rpc_url ) ) {
			$rpc_option->delete();
		} else {
			$rpc_option->update( $rpc_url );
		}
	}
}

/**
 * RPC URL提供者の利用規約に同意したかどうかを取得または設定するクラス
 *
 * @internal
 */
class AgreedRpcProviderTerms {
	/**
	 * 指定したRPC URL提供者の利用規約に同意したかどうかを取得します。
	 *
	 * @param RpcUrlProviderType $rpc_url_provider
	 * @return bool
	 */
	public function get( RpcUrlProviderType $rpc_url_provider ): bool {
		$is_agreed = ( new OptionFactory() )->agreedRpcProviderTerms( $rpc_url_provider )->get( false );    // デフォルト値はfalse(未同意)
		assert( is_bool( $is_agreed ) );    // デフォルト引数をbool型で渡しているため、必ずbool型で返ってくる

		return $is_agreed;
	}
}
