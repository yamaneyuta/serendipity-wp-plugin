<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Retry;

class Retryer {
	/**
	 * 指定されたコールバックをリトライ処理ありで実行します。
	 *
	 * @param callable $callable
	 * @param int[]    $intervals_ms
	 */
	public function execute( callable $callable, array $intervals_ms ) {
		// $intervalsの要素が0の場合は即座に実行
		if ( empty( $intervals_ms ) ) {
			return $callable();
		}

		// 以下、リトライありでの実行
		$attempts = count( $intervals_ms );
		for ( $i = 0; $i <= $attempts; $i++ ) {
			try {
				return $callable();
			} catch ( \Exception $e ) {
				if ( $i < $attempts ) {
					usleep( $intervals_ms[ $i ] * 1000 ); // ミリ秒をマイクロ秒に変換
				} else {
					throw $e; // 最後の試行で失敗した場合は例外を再スロー
				}
			}
		}
	}
}

/*
動作確認用コード
$ret = (new Retryer())->execute(function () {
	echo date("Y-m-d H:i:s") . "\n";
	throw new \Exception("test");
	// return 123;
}, [1000, 2000, 4000]);
echo "ret: " . var_export($ret, true) . "\n";
// */
