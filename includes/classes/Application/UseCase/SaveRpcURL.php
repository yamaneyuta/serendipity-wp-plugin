<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Factory\ChainRepositoryFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use wpdb;

class SaveRpcURL {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	public function handle( ChainID $chain_id, ?string $rpc_url ): void {
		$chain_repository = ( new ChainRepositoryFactory( $this->wpdb ) )->create();

		$chain = $chain_repository->getChain( $chain_id );
		assert( null !== $chain, "[517E4144] Chain with ID {$chain_id->value()} does not exist." );

		// RPC URLプロパティに新しい値を設定
		$chain->setRpcURL( $rpc_url );

		// チェーン情報を保存
		$chain_repository->save( $chain );
	}
}
