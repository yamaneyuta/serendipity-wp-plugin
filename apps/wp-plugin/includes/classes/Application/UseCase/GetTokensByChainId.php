<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\TokenDto;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

/** 指定したチェーンIDに存在するトークン一覧を取得します */
class GetTokensByChainId {

	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository = $token_repository;
	}

	private TokenRepository $token_repository;

	/** @return TokenDto[] */
	public function handle( int $chain_id ): array {
		$tokens = ( new TokensFilter() )
			->byChainID( new ChainID( $chain_id ) )
			->apply( $this->token_repository->all() );

		return array_map( fn( $token ) => TokenDto::fromEntity( $token ), $tokens );
	}
}
