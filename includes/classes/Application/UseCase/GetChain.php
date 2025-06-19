<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\ChainDto;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

/** 指定したチェーンIDの情報を取得します */
class GetChain {

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	private ChainRepository $chain_repository;

	public function handle( int $chain_id ): ChainDto {
		return ChainDto::fromEntity( $this->chain_repository->get( new ChainID( $chain_id ) ) );
	}
}
