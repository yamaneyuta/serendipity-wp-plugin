<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Web3\TokenClient;

class SaveERC20Token {
	public function __construct( TokenRepository $token_repository, ChainRepository $chain_repository ) {
		$this->token_repository = $token_repository;
		$this->chain_repository = $chain_repository;
	}

	private TokenRepository $token_repository;
	private ChainRepository $chain_repository;

	public function handle( ChainID $chain_ID, Address $address, bool $is_payable ): void {
		$token = $this->token_repository->get( $chain_ID, $address );
		if ( null === $token ) {
			// トークンデータが存在しない場合は新規登録を行うために少数点以下桁数とシンボルを取得する

			// チェーンに接続してERC20コントラクトから少数点以下桁数とシンボルを取得する
			$chain        = $this->chain_repository->get( $chain_ID );
			$token_client = new TokenClient( $chain->rpcURL(), $address );
			$decimals     = $token_client->decimals();
			$symbol       = $token_client->symbol();

			$token = new Token( $chain_ID, $address, $symbol, $decimals, $is_payable );
		} else {
			$token->setIsPayable( $is_payable );
		}

		// トークン情報を保存
		$this->token_repository->save( $token );
	}
}
