<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\Option;
use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;

/**
 * 管理者が設定した購入者が支払い可能なトークン一覧を取得または保存するクラス。
 */
class PayableSymbols {

	/**
	 * optionsテーブルへデータを保存または取得するためのオブジェクトを取得します。
	 *
	 * @param int $chain_ID
	 * @return Option
	 */
	private function getOption( int $chain_ID ): Option {
		return ( new OptionFactory() )->payableSymbols( $chain_ID );
	}

	/**
	 * 指定したチェーンIDで購入可能なトークン一覧を取得します。
	 *
	 * @param int $chain_ID
	 * @return array
	 */
	public function get( int $chain_ID ): array {
		return $this->getOption( $chain_ID )->get( array() );
	}

	/**
	 * 指定したチェーンIDで購入可能なトークン一覧を保存します。
	 *
	 * @param int   $chain_ID
	 * @param array $symbols
	 */
	public function save( int $chain_ID, array $symbols ): void {
		// 引数チェック
		$this->checkSaveParams( $chain_ID, $symbols );

		// 保存
		$this->getOption( $chain_ID )->update( $symbols );
	}

	/**
	 * 保存時の引数チェックを行います。
	 * 指定した通貨シンボルが対象のチェーンIDに存在しない場合は例外をスローします。
	 *
	 * @param int   $chain_ID
	 * @param array $symbols
	 */
	private function checkSaveParams( int $chain_ID, array $symbols ): void {
		$all_symbols = ( new TokenData() )->getAllSymbols( $chain_ID );
		foreach ( $symbols as $symbol ) {
			if ( ! in_array( $symbol, $all_symbols, true ) ) {
				throw new \InvalidArgumentException( '[1665F10A] Invalid symbol: ' . $symbol );
			}
		}
	}
}
