<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class Confirmations {
	/**
	 * 指定したチェーンの待機ブロックを取得します。
	 *
	 * @return int|string|null
	 */
	public function get( int $chain_ID ) {
		$confirmations = ( new OptionFactory() )->confirmations( $chain_ID )->get();
		if ( is_null( $confirmations ) ) {
			return null;
		} elseif ( Judge::isBlockTagName( $confirmations ) ) {
			return $confirmations;
		} else {
			return (int) $confirmations;
		}
	}

	/**
	 * 指定したチェーンの待機ブロックを設定します。
	 *
	 * @param int        $chain_ID
	 * @param int|string $confirmations
	 */
	public function set( int $chain_ID, $confirmations ): bool {
		if ( ! is_int( $confirmations ) && ! Judge::isBlockTagName( $confirmations ) ) {
			// 待機ブロックがint型でもブロックタグ名でもない場合は例外をスロー
			throw new \InvalidArgumentException( "[67FE810C] confirmations is not int or block tag name. confirmations: {$confirmations}" );
		}

		return ( new OptionFactory() )->confirmations( $chain_ID )->update( $confirmations );
	}
}
