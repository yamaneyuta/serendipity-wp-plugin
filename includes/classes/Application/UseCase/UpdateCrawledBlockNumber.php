<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Factory\AppContractRepositoryFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use wpdb;

/**
 * ペイウォール解除イベントのクロール済みブロック番号を更新します
 */
class UpdateUnlockPaywallEventCrawledBlockNumber {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function handle( ChainID $chain_id, BlockNumber $crawled_block_number ): void {
		$app_contract = ( new AppContractRepositoryFactory( $this->wpdb ) )->create()->get( $chain_id );

		// 現在のブロック番号をクロール済みとして更新する
		$app_contract->setCrawledBlockNumber( $crawled_block_number );

		// 更新されたクロール済みブロック番号を保存
		( new AppContractRepositoryFactory( $this->wpdb ) )->create()->save( $app_contract );
	}
}
