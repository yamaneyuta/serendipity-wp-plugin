<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject;

final class LogCategory {
	/** アプリケーションログ */
	private const APP = 1;
	/** 監査ログ */
	private const AUDIT = 2;

	private static array $names = array(
		self::APP   => 'app',
		self::AUDIT => 'audit',
	);

	private function __construct( int $log_category_value ) {
		assert( isset( self::$names[ $log_category_value ] ), "[1B8E9D69] Invalid log category value: {$log_category_value}" );
		$this->log_category_value = $log_category_value;
	}

	private int $log_category_value;

	public function name(): string {
		return self::$names[ $this->log_category_value ];
	}

	/** アプリケーションログ */
	public static function app(): self {
		return new self( self::APP );
	}
	/** 監査ログ */
	public static function audit(): self {
		return new self( self::AUDIT );
	}

	public function equals( self $other ): bool {
		return $this->log_category_value === $other->log_category_value;
	}
	public function __toString(): string {
		return $this->name();
	}
}
