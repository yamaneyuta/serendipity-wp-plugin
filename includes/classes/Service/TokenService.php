<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Lib\Web3\TokenClient;
use Cornix\Serendipity\Core\Repository\ChainRepository;
use Cornix\Serendipity\Core\Repository\TokenRepository;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\ChainID;

class TokenService {

	public function __construct() {
		$this->token_repository = new TokenRepository();
		$this->chain_repository = new ChainRepository();
	}
	private TokenRepository $token_repository;
	private ChainRepository $chain_repository;

	/**
	 * ERC20トークンの情報を保存します。
	 */
	public function saveERC20Token( ChainID $chain_ID, Address $address, bool $is_payable ): Token {

		$token = $this->token_repository->get( $chain_ID, $address );
		if ( null === $token ) {
			// トークンデータが存在しない場合は新規登録を行うために少数点以下桁数とシンボルを取得する

			// チェーンに接続してERC20コントラクトから少数点以下桁数とシンボルを取得する
			$chain        = $this->chain_repository->getChain( $chain_ID );
			$token_client = new TokenClient( $chain->rpcURL(), $address );
			$decimals     = $token_client->decimals();
			$symbol       = $token_client->symbol();

			$token = new Token( $chain_ID, $address, $symbol, $decimals, $is_payable );
		} else {
			$token->setIsPayable( $is_payable );
		}

		// トークン情報を保存
		$this->token_repository->save( $token );

		return $token;
	}
}
