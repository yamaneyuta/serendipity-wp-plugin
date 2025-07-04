<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestLib\PHPUnit;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;
use DI\Container;
use Hardhat;
use HardhatAccount;

/** ブロックチェーンへのアクセスが発生するテストケース */
class BlockchainTestCaseBase extends UnitTestCaseBase {

	/** @inheritdoc */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// プライベートネットを待機。全テストで1回行えばよいため、グローバル変数を使って状態を管理
		global $is_privatenet_ready;
		if ( ! $is_privatenet_ready ) {
			$hardhat_handler = new HardhatHandler( self::container() );
			$hardhat_handler->waitForNetwork();
			$hardhat_handler->waitForContractReady();
			$is_privatenet_ready = true;
		}
	}

	/** @inheritdoc */
	public function setUp(): void {
		parent::setUp();
		// ここに必要なセットアップ処理を追加

		$this->hardhat_handler = new HardhatHandler( $this->container() );
		$this->hardhat_handler->setUp();
	}

	/** @inheritdoc */
	public function tearDown(): void {
		parent::tearDown();
		// ここに必要なクリーンアップ処理を追加

		$this->hardhat_handler->tearDown();
	}

	private ?HardhatHandler $hardhat_handler;
}


class HardhatHandler {

	public function __construct( Container $container ) {
		$this->chains = ( new ChainsFilter() )
			->byNetworkCategoryID( NetworkCategoryID::privatenet() )
			->apply( $container->get( ChainRepository::class )->all() );
	}
	/** @var Chain[] */
	private array $chains;

	/**
	 * スナップショットを復元するためのコールバック
	 *
	 * @var callback[]
	 */
	private array $restore_snapshot_callbacks = array();

	public function setUp(): void {
		assert( empty( $this->restore_snapshot_callbacks ), '[5A506A5A] restore_snapshot_callbacks is not empty.' );
		foreach ( $this->chains as $chain ) {
			$rpc_url = $chain->rpcURL();
			assert( ! is_null( $rpc_url ) );
			$hardhat                            = new Hardhat( $rpc_url->value() );
			$snapshot_id                        = $hardhat->snapshot();
			$this->restore_snapshot_callbacks[] = fn() => $hardhat->revert( $snapshot_id );
		}
	}

	public function tearDown(): void {
		assert( ! empty( $this->restore_snapshot_callbacks ), '[C09DF3F1] restore_snapshot_callbacks is empty.' );
		foreach ( $this->restore_snapshot_callbacks as $callback ) {
			$callback();
		}
	}

	public function waitForNetwork(): void {
		foreach ( $this->chains as $chain ) {
			$rpc_url = $chain->rpcURL();
			assert( ! is_null( $rpc_url ) );

			// cURLでステータス200が取得できるまで最大60秒待機
			for ( $i = 0; $i < 60; $i++ ) {
				$response = wp_remote_get( $rpc_url->value() );
				$code     = wp_remote_retrieve_response_code( $response );
				if ( 200 === $code ) {
					return;
				}
				error_log( '[C215F287] Wait for network. rpc url: ' . $rpc_url->value() . ", code: $code" );
				sleep( 1 );
			}
			throw new \RuntimeException( '[BE27EE91] Failed to wait for network ready. rpc url: ' . $rpc_url->value() );
		}
	}

	public function waitForContractReady(): void {
		foreach ( $this->chains as $chain ) {
			$rpc_url = $chain->rpcURL();
			assert( ! is_null( $rpc_url ) );

			// コントラクトデプロイ後、特定のアドレスの残高が増えるので、それを確認するまで待機
			$blockchain = new BlockchainClient( $rpc_url->value() );
			for ( $i = 0; $i < 60; $i++ ) {
				$balance_hex = $blockchain->getBalanceHex( ( new HardhatAccount() )->marker() );
				if ( hexdec( $balance_hex ) > 0 ) {
					return;
				}
				error_log( '[8C4C0262] Wait for contract ready. rpc url: ' . $rpc_url->value() . ", balance: $balance_hex" );
				sleep( 1 );
			}

			throw new \RuntimeException( '[764D018F] Failed to wait for contract ready. rpc url: ' . $rpc_url->value() );
		}
	}
}
