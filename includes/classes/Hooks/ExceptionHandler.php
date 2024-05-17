<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Logger\Logger;

class ExceptionHandler {

	/** コンストラクタ */
	public function __construct() {
		$this->initialize();
	}

	private function initialize(): void {
		// 現在設定されている例外ハンドラを取得
		$prev_handler = set_exception_handler( null );

		// 例外が発生したときのコールバック関数
		$exception_callback = function ( \Throwable $e ) use ( $prev_handler ) {

			// ログにエラーを記録
			Logger::error( $e );

			if ( $prev_handler ) {
				// 以前の例外ハンドラが存在する場合はそれを実行
				$prev_handler( $e );
			} else {
				// 以前の例外ハンドラが存在しない場合は、例外を再スロー
				throw $e;
			}
		};

		// 例外ハンドラを設定
		set_exception_handler( $exception_callback );
	}
}
