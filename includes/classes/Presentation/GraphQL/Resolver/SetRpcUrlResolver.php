<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class SetRpcUrlResolver extends ResolverBase {

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	private ChainRepository $chain_repository;

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		$chain_ID = new ChainID( $args['chainID'] );
		/** @var string|null */
		$rpc_url = $args['rpcURL'] ?? null;

		Validate::checkHasAdminRole(); // 管理者権限を持っているかどうかをチェック
		( ! is_null( $rpc_url ) ) && Validate::checkURL( $rpc_url );

		// RPC URLを登録する場合は実際にアクセスしてチェーンIDを取得し、
		// 引数のチェーンIDと一致していることを確認する
		if ( ! is_null( $rpc_url ) ) {
			$actual_chain_ID_hex = ( new BlockchainClient( $rpc_url ) )->getChainIDHex();
			if ( HexFormat::toHex( $chain_ID->value() ) !== $actual_chain_ID_hex ) {
				throw new \InvalidArgumentException( '[0AD91082] Invalid chain ID. expected: ' . var_export( HexFormat::toHex( $chain_ID->value() ), true ) . ', actual: ' . var_export( $actual_chain_ID_hex, true ) );
			}
		}

		// RPC URLを保存
		try {
			global $wpdb;
			$wpdb->query( 'START TRANSACTION' );

			// リポジトリからチェーン情報を取得、RPC URLを設定して保存
			$chain = $this->chain_repository->get( $chain_ID );
			$chain->setRpcURL( $rpc_url );
			$this->chain_repository->save( $chain );

			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		return true;
	}
}
