<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\ArrayOption;
use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Types\TokenType;

/**
 * 管理者が設定した購入者が支払い可能なトークン一覧を取得または保存するクラス。
 */
class PayableTokens {

	/**
	 * optionsテーブルへデータを保存または取得するためのオブジェクトを取得します。
	 *
	 * @param int $chain_ID
	 * @return ArrayOption
	 */
	private function getPayableTokenAddressesOption( int $chain_ID ): ArrayOption {
		return ( new OptionFactory() )->payableTokenAddresses( $chain_ID );
	}

	/**
	 * 指定したチェーンIDで購入可能なトークン一覧を取得します。
	 *
	 * @param int $chain_ID
	 * @return TokenType[]
	 */
	public function get( int $chain_ID ): array {
		/** @var string[] */
		$token_addresses = $this->getPayableTokenAddressesOption( $chain_ID )->get( array() );

		// Tokenオブジェクトに変換
		return array_map( fn( $token_address ) => ( new TokenData() )->get( $chain_ID, $token_address )[0], $token_addresses );
	}

	/**
	 * 指定したチェーンIDで購入可能なトークン一覧を保存します。
	 *
	 * @param int         $chain_ID
	 * @param TokenType[] $tokens
	 */
	public function save( int $chain_ID, array $tokens, ?bool $autoload = null ): void {
		// 引数チェック
		foreach ( $tokens as $token ) {
			if ( $token->chainID() !== $chain_ID ) {
				throw new \InvalidArgumentException(
					'[D1A1D1A1] Invalid token. chain id: ' . $token->chainID() .
					', address: ' . $token->address() .
					', symbol: ' . $token->symbol()
				);
			}
		}

		// 保存時はトークンアドレスのみ保存
		/** @var string[] */
		$token_addresses = array_values( array_map( fn( $token ) => $token->address(), $tokens ) );
		$this->getPayableTokenAddressesOption( $chain_ID )->update( $token_addresses, $autoload );
	}

	/**
	 * 支払時に使用可能なトークンとして登録済みかどうかを取得します。
	 *
	 * @param TokenType $token
	 */
	public function exists( TokenType $token ): bool {
		/** @var TokenType[] */
		$tokens = $this->get( $token->chainID() );

		$token_address = $token->address();
		return array_reduce(
			$tokens,
			function ( $carry, $t ) use ( $token_address ) {
				return $carry || $t->address() === $token_address;
			},
			false
		);
	}
}
