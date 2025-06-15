<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Factory\ChainRepositoryFactory;
use Cornix\Serendipity\Core\Application\Factory\TokenRepositoryFactory;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Web3\TokenClient;
use wpdb;

class SaveERC20Token {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;


	public function handle( ChainID $chain_ID, Address $address, bool $is_payable ): void {
		$token_repository = ( new TokenRepositoryFactory( $this->wpdb ) )->create();
		$chain_repository = ( new ChainRepositoryFactory( $this->wpdb ) )->create();

		$token = $token_repository->get( $chain_ID, $address );
		if ( null === $token ) {
			// トークンデータが存在しない場合は新規登録を行うために少数点以下桁数とシンボルを取得する

			// チェーンに接続してERC20コントラクトから少数点以下桁数とシンボルを取得する
			$chain        = $chain_repository->getChain( $chain_ID );
			$token_client = new TokenClient( $chain->rpcURL(), $address );
			$decimals     = $token_client->decimals();
			$symbol       = $token_client->symbol();

			$token = new Token( $chain_ID, $address, $symbol, $decimals, $is_payable );
		} else {
			$token->setIsPayable( $is_payable );
		}

		// トークン情報を保存
		$token_repository->save( $token );
	}
}
