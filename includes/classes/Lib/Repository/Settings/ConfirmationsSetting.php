<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Settings;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;

/**
 * ユーザーが(管理画面で)設定した待機ブロック数を取得または設定するクラス
 */
class ConfirmationsSetting {
	/**
	 * 指定したチェーンの待機ブロック数(ユーザーが設定した値)を取得します。
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
	 * 指定したチェーンの待機ブロック数(ユーザーが設定した値)を設定します。
	 * 待機ブロック数を削除する場合はnullを指定します。
	 *
	 * @param int             $chain_ID
	 * @param int|string|null $confirmations
	 */
	public function set( int $chain_ID, $confirmations ): void {
		if ( ! is_null( $confirmations ) && ! is_int( $confirmations ) && ! Judge::isBlockTagName( $confirmations ) ) {
			// 待機ブロックがnullでもint型でもブロックタグ名でもない場合は例外をスロー
			throw new \InvalidArgumentException( "[67FE810C] confirmations is not null or int or block tag name. confirmations: {$confirmations}" );
		}

		$option = ( new OptionFactory() )->confirmations( $chain_ID );

		is_null( $confirmations ) ? $option->delete() : $option->update( (string) $confirmations );
	}
}
