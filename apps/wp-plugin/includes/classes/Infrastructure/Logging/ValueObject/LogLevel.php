<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject;

/** ログレベルを表すクラス */
class LogLevel {

	// ログレベルの定義
	// 値が大きいほど詳細なログを表す
	private const NONE  = 0;
	private const ERROR = 100;
	private const WARN  = 200;
	private const INFO  = 300;
	private const DEBUG = 400;

	private static array $levels = array(
		self::NONE  => 'NONE',
		self::ERROR => 'ERROR',
		self::WARN  => 'WARN',
		self::INFO  => 'INFO',
		self::DEBUG => 'DEBUG',
	);

	private function __construct( int $log_level_value ) {
		assert( isset( self::$levels[ $log_level_value ] ), "[CFB9AB28] Invalid log level value: {$log_level_value}" );
		$this->log_level_value = $log_level_value;
	}

	private int $log_level_value;

	public function name(): string {
		assert( isset( self::$levels[ $this->log_level_value ] ), "[E1304104] Invalid log level: {$this->log_level_value}" );
		return self::$levels[ $this->log_level_value ];
	}

	public static function from( string $log_level_name ): self {
		$log_level_name = strtoupper( $log_level_name );
		foreach ( self::$levels as $value => $name ) {
			if ( $name === $log_level_name ) {
				return new self( $value );
			}
		}
		throw new \InvalidArgumentException( "[B36E6C92] Invalid log level name: {$log_level_name}" );
	}

	/**
	 * このインスタンスのログレベル設定に基づき、指定されたログレベルのログを出力すべきかどうかを返します。
	 *
	 * 例: このインスタンスのログレベルがINFOの場合
	 *   - DEBUG => false（出力しない）
	 *   - ERROR => true（出力する）
	 *
	 * @param LogLevel $log_level 判定対象のログレベル
	 * @return bool 指定されたログレベルを出力すべき場合はtrue、そうでなければfalse
	 */
	public function allows( LogLevel $log_level ): bool {
		return $this->log_level_value >= $log_level->log_level_value;
	}


	public static function debug(): self {
		return new self( self::DEBUG );
	}
	public static function info(): self {
		return new self( self::INFO );
	}
	public static function warn(): self {
		return new self( self::WARN );
	}
	public static function error(): self {
		return new self( self::ERROR );
	}
	public static function none(): self {
		return new self( self::NONE );
	}

	public function equals( LogLevel $other ): bool {
		return $this->log_level_value === $other->log_level_value;
	}

	public function __toString(): string {
		return $this->name();
	}
}
