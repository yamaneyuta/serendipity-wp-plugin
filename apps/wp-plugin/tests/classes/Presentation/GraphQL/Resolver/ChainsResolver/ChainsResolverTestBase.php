<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Test\Presentation\GraphQL\Resolver\ChainsResolver;

use Cornix\Serendipity\Core\Constant\ChainIdValue;
use Cornix\Serendipity\TestLib\PHPUnit\UnitTestCaseBase;

class ChainsResolverTestBase extends UnitTestCaseBase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::resetDatabase(); // データベースをリセット
	}

	protected const CONNECTABLE_CHAIN_ID     = ChainIdValue::PRIVATENET_L1;   // プライベートネットのRPC URLは初期状態で設定済み
	protected const NOT_CONNECTABLE_CHAIN_ID = ChainIdValue::ETH_MAINNET; // メインネットのRPC URLは初期状態でNULL
	protected const INVALID_CHAIN_ID         = 999999; // 存在しないチェーンID

	/** チェーン一覧を取得する最低限のクエリ */
	protected const CHAINS_SIMPLE_QUERY = <<<GRAPHQL
		mutation GetChains(\$filter: ChainsFilter) {
			chains(filter: \$filter) {
				id
			}
		}
	GRAPHQL;


	/** チェーン一覧を取得する各パスを含むクエリ */
	protected const CHAINS_FULL_QUERY = <<<GRAPHQL
		mutation GetChains(\$filter: ChainsFilter) {
			chains(filter: \$filter) {
				id
				appContract {
					address
				}
				confirmations
				rpcURL
				tokens {
					# chain -> 取得不要
					address
					symbol
					isPayable
				}
				networkCategory {
					id
					# chains -> 取得不要
					sellableSymbols
				}
			}
		}
	GRAPHQL;
}
