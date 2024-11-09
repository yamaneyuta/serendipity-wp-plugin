<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Definition\Oracle\OracleEthMainnetDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\AnkrRpcUrlDefinition;
use Cornix\Serendipity\Core\Lib\Web3\OracleClient;
use Cornix\Serendipity\Core\Types\SymbolPair;

class OracleEthMainnetDefinitionTest extends WP_UnitTestCase {

	/**
	 * OracleEthMainnetDefinitionに定義されている内容の整合性チェック
	 *
	 * @test
	 * @testdox [F3BF1D88] OracleEthMainnetDefinition::getAddress - base: $base, quote: $quote, expected: $expected
	 * @dataProvider dataProviderForGetAddress
	 */
	public function getAddress( string $base, string $quote, ?string $expected ) {
		if ( ! ExternalApiAccess::isTesting() ) {
			$this->markTestSkipped( '[27C6CC97] Skip external access test.' );
			return;
		}

		// ARRANGE
		$sut     = new OracleEthMainnetDefinition();
		$rpc_url = ( new AnkrRpcUrlDefinition() )->get( ChainID::ETH_MAINNET );

		// ACT
		$result = $sut->getAddress( new SymbolPair( $base, $quote ) );
		if ( ! is_null( $result ) ) {
			// AnkrのRPC URLに接続してOracleから最新レートと説明を取得
			$oracle_client = new OracleClient( $rpc_url, $result );
			usleep( 20 * 1000 ); // 20ms 待機(Ankrは50回/秒の制限があるため)
			$last_round_data = $oracle_client->latestRoundData();
			$updated_at      = intval( $last_round_data->updatedAt()->toString() );
			usleep( 20 * 1000 ); // 20ms 待機
			$description = $oracle_client->description();
		}

		// ASSERT
		$this->assertEquals( $expected, $result );
		if ( ! is_null( $result ) ) {
			// 48時間以内に更新されていることを確認
			$this->assertGreaterThan( time() - 48 * 60 * 60, $updated_at, "address: {$result}, updated_at: {$updated_at}" );
			// 説明が通貨ペアと一致することを確認
			$this->assertEquals( $base . ' / ' . $quote, $description, "address: {$result}, description: {$description}" );
		} else {
			$this->assertNull( $updated_at );
			$this->assertNull( $description );
		}
	}
	public function dataProviderForGetAddress(): array {
		try {
			$data = array();

			if ( ExternalApiAccess::isTesting() ) {
				$feeds = json_decode( file_get_contents( 'https://reference-data-directory.vercel.app/feeds-mainnet.json' ), true );
				$feeds = array_filter(
					$feeds,
					fn( $feed ) =>
						in_array( $feed['docs']['assetClass'], array( 'Fiat', 'Crypto' ) ) &&
						is_string( $feed['docs']['baseAsset'] ) &&
						is_string( $feed['docs']['quoteAsset'] ) &&
						in_array( $feed['docs']['quoteAsset'], array( 'USD', 'ETH' ) ) &&
						$feed['name'] === ( $feed['docs']['baseAsset'] . ' / ' . $feed['docs']['quoteAsset'] )
				);
				$feeds = array_values( $feeds );
			}

			foreach ( $feeds as $feed ) {
				$base          = $feed['docs']['baseAsset'];
				$quote         = $feed['docs']['quoteAsset'];
				$proxy_address = $feed['proxyAddress'];
				$hidden        = $feed['docs']['hidden'] ?? false;

				$expected = $hidden ? null : $proxy_address;
				$data[]   = array( $base, $quote, $expected );
			}

			return $data;
		} catch ( Throwable $e ) {
			error_log( $e->getMessage() );
			error_log( $e->getTraceAsString() );
			exit( 1 );
		}
	}


	/**
	 * 法定通貨のシンボル一覧を取得するテスト
	 *
	 * @test
	 * @testdox [4325F6C1] OracleEthMainnetDefinition::fiatSymbols
	 */
	public function fiatSymbols() {
		// ARRANGE
		$sut      = new OracleEthMainnetDefinition();
		$expected = array( 'AUD', 'CAD', 'CHF', 'CNY', 'EUR', 'GBP', 'JPY', 'KRW', 'NZD', 'SGD', 'TRY' );

		// ACT
		$result = $sut->fiatSymbols();

		// ASSERT
		$this->assertEqualsCanonicalizing( $result, $expected );
	}
}
