<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\RpcUrlDefinitionBase;
use Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL\AnkrRpcUrlDefinition;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClient;

class RpcUrlTest extends WP_UnitTestCase {
	/**
	 * 処理のテストではなく、実装漏れの確認を行うためのテスト
	 * `includes/classes/Lib/Repository/Definition/RpcURL/*.php`に定義されたRPC URLが接続可能かつチェーンIDが正しいことを確認
	 *
	 * @test
	 * @testdox [7D099EB9] check all RpcURL definition. class: $class_name, chain: $chain_id, url: $rpc_url
	 * @dataProvider classNameAndChainIDs
	 */
	public function getRpcUrlsConnectable( string $class_name, int $chain_id, string $rpc_url ) {
		if ( ! ExternalApiAccess::isTesting() ) {
			$this->markTestSkipped( '[16DB1148] Skip external access test.' );
			return;
		}
		// // ARRANGE
		// // Do nothing.

		// ACT
		$chain_id_hex = ( new BlockchainClient( $rpc_url ) )->getChainIDHex();

		// ASSERT
		$this->assertEquals( Hex::from( $chain_id ), $chain_id_hex );
	}
	public function classNameAndChainIDs() {
		try {
			$class_names   = $this->getDefinitionClassNames();
			$all_chain_ids = ( new TestAllChainID() )->get();
			$result        = array();

			foreach ( $class_names as $class_name ) {
				$class_path = $this->toClassPath( $class_name );
				/** @var RpcUrlDefinitionBase */
				$definition = new $class_path();
				foreach ( $all_chain_ids as $chain_id ) {
					$url = $definition->get( $chain_id );
					if ( ! is_null( $url ) ) {
						$result[] = array( $class_name, $chain_id, $url );
					}
				}
			}

			return $result;
		} catch ( \Throwable $e ) {
			error_log( $e->getMessage() );
			error_log( $e->getTraceAsString() );
			exit( 1 );
		}
	}
	/**
	 * 定義を格納しているディレクトリから、定義を行っているクラス名一覧を取得します。
	 *
	 * @return string[]
	 */
	private function getDefinitionClassNames(): array {
		$files = glob( __DIR__ . '/../../../../../includes/classes/Lib/Repository/Definition/RpcURL/*Definition.php' );
		if ( ! is_array( $files ) || count( $files ) == 0 ) {
			throw new \RuntimeException( '[A12CAEFC] No definition files' );
		}

		return array_map( fn( $file )=>basename( $file, '.php' ), $files );
	}
	private function toClassPath( string $definition_class_name ): string {
		$reflection = new \ReflectionClass( AnkrRpcUrlDefinition::class );
		return $reflection->getNamespaceName() . '\\' . $definition_class_name;
	}
}
