<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Logger;

use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Database\DataType\LogData;
use Cornix\Serendipity\Core\Env\Env;
use Cornix\Serendipity\Core\PluginMainFile;
use Cornix\Serendipity\Core\Utils\Constants;

class Logger {

	private function __construct() {
		try {
			// 現在設定されているログレベルを取得
			self::$log_level = Database::getLogLevel( 'server', true );
			if ( is_null( self::$log_level ) ) {
				error_log( '{58D62CF6-DA00-410F-BDB3-E35EE3ACEBFE}' );
				self::$log_level = 'debug';
			}

			// 設定可能なログレベル一覧を取得
			/** @var string[] */
			$log_levels       = Constants::get( 'logLevels' );
			self::$log_levels = $log_levels;
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
			self::$log_level  = 'debug';
			self::$log_levels = array( 'debug', 'info', 'warn', 'error' );
			// ここで例外を送出すると無限ループに陥る可能性があるため、再スローしない
		}
	}
	private static function getInstance() {
		static $instance;
		if ( ! isset( $instance ) ) {
			$instance = new Logger();
		}
		return $instance;
	}

	/**
	 * 現在のログレベル(データベースに記録されているログレベル)
	 *
	 * @var string
	 */
	private static $log_level;

	/**
	 * 設定可能なログレベル一覧
	 *
	 * @var string[]
	 */
	private static $log_levels;


	public static function debug( $message ) {
		self::getInstance()->log( 'debug', $message );
	}

	public static function info( $message ) {
		self::getInstance()->log( 'info', $message );
	}

	public static function warn( $message ) {
		self::getInstance()->log( 'warn', $message );
	}

	public static function error( $message ) {
		self::getInstance()->log( 'error', $message );
	}

	private static function getLevelIndex( string $log_level ) {
		$index = array_search( $log_level, self::$log_levels, true );
		if ( false === $index ) {
			throw new \InvalidArgumentException( "Invalid log level: $log_level" );
		}
		return $index;
	}

	/**
	 * ログを書き込むべきかどうかを判定します。
	 *
	 * @param string $log_level
	 * @return bool ログを書き込むべきならtrue、そうでないならfalse
	 */
	private static function shouldWriteLog( string $log_level ) {
		$level_index        = self::getLevelIndex( $log_level );
		$server_level_index = self::getLevelIndex( self::$log_level );
		return $level_index >= $server_level_index;
	}

	/**
	 *
	 * @param string $log_level
	 * @param mixed  $message
	 * @return void
	 */
	private function log( string $log_level, $message ) {

		try {
			// ログを書き込むべきでない場合は何もしない
			if ( ! self::shouldWriteLog( $log_level ) ) {
				return;
			}

			// データベースに書き込むためのデータを作成
			$log_data = $this->createLogData( $log_level, $message );

			// 開発時は標準エラーにも出力
			if ( Env::isDevelopmentMode() ) {
				error_log( "[$log_level] " . $log_data->log_message );
			}

			// ログをデータベースに書き込む
			Database::log( array( $log_data ) );
		} catch ( \Exception $e ) {
			try {
				error_log( 'log error: ' . $e->getMessage() );
				error_log( "[$log_level] " . var_export( $message, true ) );
			} catch ( \Exception $e2 ) {
			}
			// ここで例外を送出すると無限ループに陥る可能性があるため、再スローしない
		}
	}

	private function createLogData( string $log_level, $message ) {
		if ( is_string( $message ) ) {
			$log_message = $message;
		}
		// $messageが例外だった場合、例外の内容をログに出力する。
		elseif ( $message instanceof \Exception ) {
			// 例外のクラス名を取得
			$exception_class_name = get_class( $message );

			$log_message =
				"[$exception_class_name]"
				. "\ncode: " . $message->getCode()
				. "\nmessage: " . $message->getMessage()
				. "\nfile: " . $message->getFile() . ':' . $message->getLine()
				. "\ntrace: " . $message->getTraceAsString();
		} else {
			$log_message = var_export( $message, true );
		}

		// ログオブジェクトを作成
		return new LogData(
			microtime( true ),
			$log_level,
			"$_SERVER[REQUEST_URI]",    // uri
			$this->getSource(), // source
			$log_message,
			PluginMainFile::getVersion(),
		);
	}

	private function getSource() {
		$trace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 4 );

		if ( isset( $trace[3] ) ) {
			$caller = $trace[3];
			$file   = isset( $caller['file'] ) ? $caller['file'] : 'Unknown File';
			$line   = isset( $caller['line'] ) ? $caller['line'] : 'Unknown Line';
			return "$file:$line";
		} else {
			return 'Unknown Source';
		}
	}
}
