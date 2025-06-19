<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Logger;

/** @deprecated */
class DeprecatedLogger {

	private static IDeprecatedLogger $instance;

	private static function logger(): IDeprecatedLogger {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new DefaultLogger();
		}
		return self::$instance;
	}

	/**
	 * ロガーインスタンスを設定します。
	 */
	public static function setLogger( IDeprecatedLogger $logger ) {
		self::$instance = $logger;
	}

	/**
	 * デバッグログを出力します。
	 *
	 * @param string|\Throwable $message_or_exception メッセージまたは例外
	 */
	public static function debug( $message ) {
		self::logger()->debug( $message );
	}

	/**
	 * 情報ログを出力します。
	 *
	 * @param string|\Throwable $message_or_exception メッセージまたは例外
	 */
	public static function info( $message ) {
		self::logger()->info( $message );
	}

	/**
	 * 警告ログを出力します。
	 *
	 * @param string|\Throwable $message_or_exception メッセージまたは例外
	 */
	public static function warn( $message ) {
		self::logger()->warn( $message );
	}

	/**
	 * エラーログを出力します。
	 *
	 * @param string|\Throwable $message_or_exception メッセージまたは例外
	 */
	public static function error( $message ) {
		self::logger()->error( $message );
	}
}


class DefaultLogger implements IDeprecatedLogger {

	public function debug( $message_or_exception ) {
		error_log( '[DEBUG] ' . $message_or_exception );
	}
	public function info( $message_or_exception ) {
		error_log( '[INFO] ' . $message_or_exception );
	}
	public function warn( $message_or_exception ) {
		error_log( '[WARN] ' . $message_or_exception );
	}
	public function error( $message_or_exception ) {
		error_log( '[ERROR] ' . $message_or_exception );
	}
}
