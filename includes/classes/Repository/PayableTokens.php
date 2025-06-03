<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Lib\Option\ArrayOption;
use Cornix\Serendipity\Core\Lib\Option\OptionFactory;
use Cornix\Serendipity\Core\Entity\Token;
use Cornix\Serendipity\Core\Entity\Tokens;
use Cornix\Serendipity\Core\Lib\Algorithm\Filter\TokensFilter;
use Cornix\Serendipity\Core\ValueObject\Address;

/**
 * 管理者が設定した購入者が支払い可能なトークン一覧を取得または保存するクラス。
 *
 * @deprecated
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
	 * @return Tokens
	 */
	public function get( int $chain_ID ): Tokens {
		$tokens_filter = ( new TokensFilter() )->byIsPayable( true );
		return $tokens_filter->apply( ( new TokenRepository() )->all() );
	}

	/**
	 * 指定したチェーンIDで購入可能なトークン一覧を保存します。
	 *
	 * @param int     $chain_ID
	 * @param Token[] $tokens
	 */
	public function save( int $chain_ID, array $tokens, ?bool $autoload = null ): void {
		// 引数チェック
		foreach ( $tokens as $token ) {
			if ( $token->chainID() !== $chain_ID ) {
				throw new \InvalidArgumentException(
					'[D1A1D1A1] Invalid token. chain id: ' . $token->chainID() .
					', address: ' . $token->address()->value() .
					', symbol: ' . $token->symbol()
				);
			}
		}

		// 保存時はトークンアドレスのみ保存
		/** @var string[] */
		$token_addresses = array_values( array_map( fn( $token ) => $token->address()->value(), $tokens ) );
		$this->getPayableTokenAddressesOption( $chain_ID )->update( $token_addresses, $autoload );
	}

	/**
	 * 支払時に使用可能なトークンとして登録済みかどうかを取得します。
	 *
	 * @param Token $token
	 */
	public function exists( Token $token ): bool {
		$tokens = $this->get( $token->chainID() );

		$token_address = $token->address();
		return array_reduce(
			$tokens->toArray(),
			function ( $carry, $t ) use ( $token_address ) {
				return $carry || $t->address()->equals( $token_address );
			},
			false
		);
	}
}
