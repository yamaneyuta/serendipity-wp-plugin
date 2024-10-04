<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Logger;

interface ILogger {
	/**
	 * デバッグログを出力します。
	 *
	 * @param string|\Throwable $message_or_exception メッセージまたは例外
	 */
	public function debug( $message_or_exception );

	/**
	 * 情報ログを出力します。
	 *
	 * @param string|\Throwable $message_or_exception メッセージまたは例外
	 */
	public function info( $message_or_exception );

	/**
	 * 警告ログを出力します。
	 *
	 * @param string|\Throwable $message_or_exception メッセージまたは例外
	 */
	public function warn( $message_or_exception );

	/**
	 * エラーログを出力します。
	 *
	 * @param string|\Throwable $message_or_exception メッセージまたは例外
	 */
	public function error( $message_or_exception );
}
