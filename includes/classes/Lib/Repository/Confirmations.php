<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Settings\ConfirmationsSetting;
use Cornix\Serendipity\Core\Lib\Repository\Settings\DefaultValue;

/**
 * チェーンIDに対する待機ブロック数を取得するクラス
 */
class Confirmations {
	/**
	 * 指定したチェーンの待機ブロック数を取得します。
	 * ユーザーが設定した値があればその値を、設定されていなければデフォルト値を返します。
	 *
	 * @return int|string
	 */
	public function get( int $chain_ID ) {
		$confirmations = ( new ConfirmationsSetting() )->get( $chain_ID );

		// 設定が存在しない場合はデフォルト値を返す
		return is_null( $confirmations ) ? ( new DefaultValue() )->confirmations( $chain_ID ) : $confirmations;
	}

	/**
	 * 指定したチェーンの待機ブロック数(ユーザーが設定した値)を設定します。
	 *
	 * @param int        $chain_ID
	 * @param int|string $confirmations
	 */
	public function set( int $chain_ID, $confirmations ): bool {
		return ( new ConfirmationsSetting() )->set( $chain_ID, $confirmations );
	}
}
