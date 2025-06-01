<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Entity\Chain;
use Cornix\Serendipity\Core\Repository\AppContractRepository;

class AppClientFactory {
	/**
	 * 指定したチェーンのAppコントラクトに接続するオブジェクトを生成します。
	 */
	public function create( Chain $chain ): AppClient {
		// チェーンに接続できない場合は例外を投げる
		if ( ! $chain->connectable() ) {
			throw new \LogicException( '[49ACED7A] Chain is not connectable. - ' . $chain->id );
		}

		// チェーンにデプロイされているAppコントラクトのアドレスを取得
		$app_contract = ( new AppContractRepository() )->get( $chain->id );
		if ( is_null( $app_contract ) ) {
			throw new \Exception( '[6D37E8B3] Contract is not found. - ' . $chain->id );
		}

		return new AppClient( $chain->rpc_url, $app_contract->address );
	}
}
