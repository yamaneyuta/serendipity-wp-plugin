<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service;

use Cornix\Serendipity\Core\Entity\Chain;
use Cornix\Serendipity\Core\Repository\ChainRepository;
use InvalidArgumentException;

/**
 * チェーンの情報を取得するクラス
 */
class ChainService {
	public function __construct( ChainRepository $repository ) {
		$this->repository = $repository;
	}
	private ChainRepository $repository;

	public function getChain( int $chain_id ): ?Chain {
		return $this->repository->getChain( $chain_id );
	}

	/**
	 * リポジトリに登録されているチェーン一覧を取得します。
	 *
	 * @return Chain[]
	 */
	public function getAllChains(): array {
		return $this->repository->getAllChains();
	}

	/**
	 * チェーン情報を更新します。
	 *
	 * @param Chain $chain
	 */
	private function saveChain( Chain $chain ): void {
		$this->repository->save( $chain );
	}

	/**
	 * 指定したチェーンの情報を更新し、保存します。
	 *
	 * @param int                  $chain_id
	 * @param callback(Chain):void $updater
	 */
	private function updatePropertyAndSave( int $chain_id, $updater ): void {
		$chain = $this->getChain( $chain_id );
		if ( $chain === null ) {
			throw new \InvalidArgumentException( "[465AB29B] Chain with ID {$chain_id} does not exist." );
		}
		$updater( $chain );
		$this->saveChain( $chain );
	}

	public function saveRpcURL( int $chain_id, ?string $rpc_url ): void {
		$this->updatePropertyAndSave( $chain_id, fn( Chain $chain ) => $chain->setRpcURL( $rpc_url ) );
	}

	/**
	 *
	 * @param int        $chain_id
	 * @param int|string $confirmations
	 */
	public function saveConfirmations( int $chain_id, $confirmations ): void {
		if ( ! is_int( $confirmations ) && ! is_string( $confirmations ) ) {
			throw new InvalidArgumentException( '[5ED6D745] Confirmations must be an integer or a string.' );
		}
		$this->updatePropertyAndSave( $chain_id, fn( Chain $chain ) => $chain->setConfirmations( $confirmations ) );
	}
}
