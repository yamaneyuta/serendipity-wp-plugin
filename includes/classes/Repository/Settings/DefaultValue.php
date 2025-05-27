<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Settings;

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

	/**
	 * `eth_getLogs`呼び出しで取得するブロック数の最大値
	 */
	public function getLogsMaxRange( int $chain_ID ): int {
		// 以下のスレッドで以下の制限があるとの記述あり
		// https://github.com/bnb-chain/bsc/issues/113
		// - BSC: 5000
		// - Alchemy: 2000 => https://docs.alchemy.com/reference/eth-getlogs
		//
		// QuickNodeの無料プランはPolygonであっても最大5ブロックしか取得できない点に注意(有料プランであれば最大10,000ブロック)
		// -> 10秒に1回以上リクエストしないと取得しきれないため、本アプリにおいては使い物にならない
		// https://www.quicknode.com/docs/polygon/eth_getLogs

		// 一旦、Alchemyの制限に合わせる
		// ブロック生成速度が2s/blockの場合、1時間分程度のログ取得が可能。(Cronのインターバルが1時間であっても1回の取得で完了できる)
		return 1999;
	}
}
