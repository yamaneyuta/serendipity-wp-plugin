<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Infrastructure\Factory\AppContractRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\ChainRepositoryFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;
use wpdb;

/**
 * クロール済みブロック番号を初期化します
 */
class InitCrawledBlockNumber {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function handle( ChainID $chain_id ): void {
		$app_contract_repository = ( new AppContractRepositoryFactory( $this->wpdb ) )->create();
		$app_contract            = $app_contract_repository->get( $chain_id );
		if ( null !== $app_contract->crawledBlockNumber() ) {
			return; // 初期化済みの場合は何もしない
		}

		// 現在のブロック番号をクロール済みとして設定したいが、
		// RPC URLのプロバイダによって誤差が生じる可能性があるため
		// ある程度のマージンを持たせてクロール済みのブロック番号を設定する

		$safe_crawled_block_number = ( new GetSafetyCrawledBlockNumber( $this->wpdb ) )->handle( $chain_id );
		$app_contract->setCrawledBlockNumber( $safe_crawled_block_number );

		// 保存
		$app_contract_repository->save( $app_contract );
	}
}

/** クロール済みブロックとして安全なブロック番号を取得します */
class GetSafetyCrawledBlockNumber {

	private const SAFETY_MARGIN_SECONDS = 60 * 5; // 5分

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function handle( ChainID $chain_id ): BlockNumber {
		$chain  = ( new ChainRepositoryFactory( $this->wpdb ) )->create()->getChain( $chain_id );
		$client = ( new BlockchainClient( $chain->rpcURL() ) );

		// 最新のブロック情報を取得
		$res                 = $client->getBlockByNumber( 'latest' );
		$latest_block_number = $res->blockNumber();
		$latest_timestamp    = $res->timestamp();

		// 1000ブロック前の情報を取得
		$target_block_number = BlockNumber::from( max( $latest_block_number->int() - 1000, 1 ) ); // マイナスにならないように調整
		$prev_res            = $client->getBlockByNumber( $target_block_number );
		$prev_block_number   = $prev_res->blockNumber();
		$prev_timestamp      = $prev_res->timestamp();

		// ブロックの平均生成時間を計算(ゼロ除算は発生し得ないためチェック不要)
		$average_block_time = ( $latest_timestamp - $prev_timestamp ) / ( $latest_block_number->int() - $prev_block_number->int() );

		// マージンを取ったブロック番号を計算
		$safety_block_number = BlockNumber::from(
			// マイナスにならないように調整
			max( $latest_block_number->int() - (int) ceil( self::SAFETY_MARGIN_SECONDS / $average_block_time ), 1 )
		);

		// confirmationsを考慮したブロック番号を計算
		$confirmations = $chain->confirmations();
		if ( is_int( $confirmations ) ) {
			$safety_block_number = BlockNumber::from(
				max( $safety_block_number->int() - $confirmations, 1 ) // マイナスにならないように調整
			);
		} elseif ( is_string( $confirmations ) ) {
			// 現在、`safe`, `finalized`などといったタグはサポートしていない
			throw new \Exception( '[D8DACAB1] Not supported confirmations type: ' . $confirmations );
		} else {
			throw new \Exception( '[FEA50B2C] Invalid confirmations type: ' . var_export( $confirmations, true ) );
		}

		return $safety_block_number;
	}
}
