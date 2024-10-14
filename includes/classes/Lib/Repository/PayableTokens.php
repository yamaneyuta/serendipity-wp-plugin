<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\ArrayOption;
use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Types\Token;

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
	private function getOption( int $chain_ID ): ArrayOption {
		return ( new OptionFactory() )->payableSymbols( $chain_ID );
	}

	/**
	 * 指定したチェーンIDで購入可能なトークン一覧を取得します。
	 *
	 * @param int $chain_ID
	 * @return Token[]
	 */
	public function get( int $chain_ID ): array {
		/** @var string[] */
		$token_addresses = $this->getOption( $chain_ID )->get( array() );

		// Tokenオブジェクトに変換
		return array_map( fn( $token_address ) => Token::from( $chain_ID, $token_address ), $token_addresses );
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
				throw new \InvalidArgumentException( '[D1A1D1A1] Invalid token: ' . get_class( $token ) );
			}
		}

		// 保存時はトークンアドレスのみ保存
		/** @var string[] */
		$token_addresses = array_map( fn( $token ) => $token->address(), $tokens );
		$this->getOption( $chain_ID )->update( $token_addresses, $autoload );
	}

	/**
	 * 支払時に使用可能なトークンとして登録済みかどうかを取得します。
	 *
	 * @param Token $token
	 */
	public function exists( Token $token ): bool {
		/** @var Token[] */
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
