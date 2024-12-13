<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Database\Schema\TokenTable;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\TokenClientFactory;
use Cornix\Serendipity\Core\Types\TokenType;

class TokenData {

	public function add( int $chain_ID, string $contract_address ): void {
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
	public function get( ?int $chain_ID = null, ?string $address = null, ?string $symbol = null ): array {
		// テーブルに保存されている、ネイティブトークンを除くトークンデータ一覧を取得
		$contract_tokens = ( new TokenTable() )->select( $chain_ID, $address, $symbol );

		// 一旦、定義されているチェーンのネイティブトークンをすべて取得(以後の処理でフィルタリングする)
		$native_tokens = array_map( fn( $chain_ID ) => TokenType::from( $chain_ID, Ethers::zeroAddress() ), ( new ChainIDs() )->get() );
		// チェーンIDが指定されている場合は、そのチェーンIDでフィルタ
		if ( ! is_null( $chain_ID ) ) {
			$native_tokens = array_filter( $native_tokens, fn( $token ) => $token->chainID() === $chain_ID );
		}
		// アドレスが指定されている場合は、そのアドレスでフィルタ
		if ( ! is_null( $address ) ) {
			$native_tokens = array_filter( $native_tokens, fn( $token ) => $token->address() === $address );
		}
		// 通貨シンボルが指定されている場合は、そのシンボルでフィルタ
		if ( ! is_null( $symbol ) ) {
			$native_tokens = array_filter( $native_tokens, fn( $token ) => $token->symbol() === $symbol );
		}

		// マージして返す
		$result = array_merge( array_values( $contract_tokens ), array_values( $native_tokens ) );

		return $result;
	}
}
