<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Lib\Database\Schema\TokenTable;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\TokenClientFactory;
use Cornix\Serendipity\Core\Types\TokenType;

class TokenData {

	public function addERC20( int $chain_ID, string $contract_address ): void {
		assert( Judge::isChainID( $chain_ID ), '[0BB33181] Invalid chain ID. - ' . $chain_ID );
		assert( Judge::isAddress( $contract_address ), '[A80ECABD] Invalid address. - ' . $contract_address );
		if ( Ethers::zeroAddress() === $contract_address ) {
			throw new \InvalidArgumentException( '[6006664F] Address is zero. - ' . $contract_address );
		}

		// TODO: アドレスのバイトコードを取得し、存在しない場合はエラーとする処理をここに追加

		// コントラクトからsymbolとdecimalsを取得
		$token_client = ( new TokenClientFactory() )->create( $chain_ID, $contract_address );
		$symbol       = $token_client->symbol();
		$decimals     = $token_client->decimals();

		Judge::checkSymbol( $symbol );
		Judge::checkDecimals( $decimals );

		// テーブルにレコードを追加
		( new TokenTable() )->insert( $chain_ID, $contract_address, $symbol, $decimals );
	}

	/**
	 * トークンデータ一覧を取得します。
	 *
	 * @param null|int    $chain_ID チェーンIDでフィルタする場合に指定
	 * @param null|string $address アドレスでフィルタする場合に指定
	 * @return TokenType[] ネイティブトークンやERC20の情報一覧
	 */
	public function select( ?int $chain_ID = null, ?string $address = null, ?string $symbol = null ): array {
		// テーブルに保存されているトークンデータ一覧を取得
		return ( new TokenTable() )->select( $chain_ID, $address, $symbol );
	}

	/**
	 * トークンデータを取得します。
	 */
	public function get( int $chain_ID, string $address ): TokenType {
		$tokens = $this->select( $chain_ID, $address );
		if ( 1 !== count( $tokens ) ) {
			throw new \InvalidArgumentException( "[E6876786] Invalid token data. - chainID: {$chain_ID}, address: {$address}, count: " . count( $tokens ) );
		}
		return $tokens[0];
	}
}
