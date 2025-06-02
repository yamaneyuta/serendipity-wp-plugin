<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Repository\TableGateway\TokenTable;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\TokenClientFactory;
use Cornix\Serendipity\Core\Entity\Token;
use Cornix\Serendipity\Core\ValueObject\Address;

class TokenData {

	public function addERC20( int $chain_ID, Address $contract_address ): void {
		assert( Validate::isChainID( $chain_ID ), '[0BB33181] Invalid chain ID. - ' . $chain_ID );
		if ( Ethers::zeroAddress()->value() === $contract_address->value() ) {
			throw new \InvalidArgumentException( '[6006664F] Address is zero. - ' . $contract_address );
		}

		// TODO: アドレスのバイトコードを取得し、存在しない場合はエラーとする処理をここに追加

		// コントラクトからsymbolとdecimalsを取得
		$token_client = ( new TokenClientFactory() )->create( $chain_ID, $contract_address );
		$symbol       = $token_client->symbol();
		$decimals     = $token_client->decimals();

		Validate::checkSymbol( $symbol );
		Validate::checkDecimals( $decimals );

		// テーブルにレコードを追加
		( new TokenTable() )->insert( $chain_ID, $contract_address, $symbol, $decimals );
	}

	/**
	 * トークンデータ一覧を取得します。
	 *
	 * @param null|int     $chain_ID チェーンIDでフィルタする場合に指定
	 * @param null|Address $address アドレスでフィルタする場合に指定
	 * @return Token[] ネイティブトークンやERC20の情報一覧
	 */
	public function select( ?int $chain_ID = null, ?Address $address = null, ?string $symbol = null ): array {
		// テーブルに保存されているトークンデータ一覧を取得
		return ( new TokenTable() )->select( $chain_ID, $address, $symbol );
	}

	/**
	 * トークンデータを取得します。
	 */
	public function get( int $chain_ID, Address $address ): Token {
		$tokens = $this->select( $chain_ID, $address );
		if ( 1 !== count( $tokens ) ) {
			throw new \InvalidArgumentException( "[E6876786] Invalid token data. - chainID: {$chain_ID}, address: {$address}, count: " . count( $tokens ) );
		}
		return $tokens[0];
	}
}
