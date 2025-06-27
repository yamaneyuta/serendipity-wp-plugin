<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use InvalidArgumentException;

/**
 * チェーンの情報を取得するクラス
 */
class ChainService {
	public function __construct( ChainRepository $repository ) {
		$this->repository = $repository;
	}
	private ChainRepository $repository;

	/** @deprecated Use ChainRepository::get */
	public function getChain( ChainID $chain_id ): ?Chain {
		return $this->repository->get( $chain_id );
	}

	/**
	 * リポジトリに登録されているチェーン一覧を取得します。
	 *
	 * @return Chain[]
	 * @deprecated Use ChainRepository::all
	 */
	public function getAllChains(): array {
		return $this->repository->all();
	}

	/**
	 * チェーン情報を更新します。
	 *
	 * @param Chain $chain
	 * @deprecated Use ChainRepository::save
	 */
	private function saveChain( Chain $chain ): void {
		$this->repository->save( $chain );
	}

	/**
	 * 指定したチェーンの情報を更新し、保存します。
	 *
	 * @param ChainID              $chain_id
	 * @param callback(Chain):void $updater
	 */
	private function updatePropertyAndSave( ChainID $chain_id, $updater ): void {
		$chain = $this->getChain( $chain_id );
		if ( $chain === null ) {
			throw new \InvalidArgumentException( "[465AB29B] Chain with ID {$chain_id->value()} does not exist." );
		}
		$updater( $chain );
		$this->saveChain( $chain );
	}

	/**
	 *
	 * @param ChainID       $chain_id
	 * @param Confirmations $confirmations
	 * @deprecated Use ChainRepository::save
	 */
	public function saveConfirmations( ChainID $chain_id, Confirmations $confirmations ): void {
		$this->updatePropertyAndSave( $chain_id, fn( Chain $chain ) => $chain->setConfirmations( $confirmations ) );
	}
}
