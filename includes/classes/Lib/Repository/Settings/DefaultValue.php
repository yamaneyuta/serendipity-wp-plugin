<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Settings;

/**
 * ユーザーが設定を行っていない場合のデフォルト値を取得するためのクラス
 */
class DefaultValue {
	/**
	 * 指定したチェーンの待機ブロック数の既定値
	 *
	 * @param int $chain_ID
	 * @return int|string
	 */
	public function confirmations( int $chain_ID ) {
		return 1;
	}
}
