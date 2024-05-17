<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Helpers;

use Cornix\Serendipity\Core\Logger\Logger;

/**
 * POSTリクエストのボディから値を安全に取得するためのクラス。
 */
class SafePropertyReader {
	public function __construct( array $request_body_json ) {
		$this->request_body_json = $request_body_json;
	}
	/** @var array */
	private $request_body_json;

	public function getIntOrNull( string $key ): ?int {
		if ( ! isset( $this->request_body_json[ $key ] ) ) {
			return null;
		}
		if ( ! is_int( $this->request_body_json[ $key ] ) ) {
			$this->throwTypeError( $key, 'int' );
		}

		return (int) $this->request_body_json[ $key ];
	}

	/**
	 * リクエストボディから数値の値を取得します。
	 */
	public function getInt( string $key ): int {
		if ( ! isset( $this->request_body_json[ $key ] ) ) {
			$this->throwNotSetError( $key );
		}
		if ( ! is_int( $this->request_body_json[ $key ] ) ) {
			$this->throwTypeError( $key, 'int' );
		}

		return (int) $this->request_body_json[ $key ];
	}

	/**
	 * @return int[]
	 */
	public function getIntArray( string $key ): array {
		if ( ! isset( $this->request_body_json[ $key ] ) ) {
			$this->throwNotSetError( $key );
		}
		if ( ! is_array( $this->request_body_json[ $key ] ) ) {
			$this->throwTypeError( $key, 'array' );
		}
		$ret = (array) $this->request_body_json[ $key ];

		foreach ( $ret as $value ) {
			if ( ! is_int( $value ) ) {
				$this->throwTypeError( $key, 'int' );
			}
		}

		return $ret;
	}

	/**
	 * リクエストボディから文字列またはnullの値を取得します。
	 */
	public function getStringOrNull( string $key ): ?string {
		if ( ! isset( $this->request_body_json[ $key ] ) ) {
			return null;
		}
		if ( ! is_string( $this->request_body_json[ $key ] ) ) {
			$this->throwTypeError( $key, 'string' );
		}

		return (string) $this->request_body_json[ $key ];
	}

	/**
	 * リクエストボディから文字列の値を取得します。
	 */
	public function getString( string $key ): string {
		if ( ! isset( $this->request_body_json[ $key ] ) ) {
			$this->throwNotSetError( $key );
		}
		if ( ! is_string( $this->request_body_json[ $key ] ) ) {
			$this->throwTypeError( $key, 'string' );
		}

		return (string) $this->request_body_json[ $key ];
	}

	/**
	 * @return string[]
	 */
	public function getStringArray( string $key ): array {
		if ( ! isset( $this->request_body_json[ $key ] ) ) {
			$this->throwNotSetError( $key );
		}
		if ( ! is_array( $this->request_body_json[ $key ] ) ) {
			$this->throwTypeError( $key, 'array' );
		}
		$ret = (array) $this->request_body_json[ $key ];

		foreach ( $ret as $value ) {
			if ( ! is_string( $value ) ) {
				$this->throwTypeError( $key, 'string' );
			}
		}

		return $ret;
	}

	/**
	 * リクエストボディから16進数形式の文字列の値を取得します。
	 */
	public function getHex( string $key ): string {
		$result = $this->getString( $key );
		if ( ! preg_match( '/^0x[0-9a-fA-F]+$/', $result ) ) {
			$this->throwTypeError( $key, 'hex' );
		}
		return $result;
	}

	public function getBool( string $key ): bool {
		if ( ! isset( $this->request_body_json[ $key ] ) ) {
			$this->throwNotSetError( $key );
		}
		if ( ! is_bool( $this->request_body_json[ $key ] ) ) {
			$this->throwTypeError( $key, 'bool' );
		}

		return (bool) $this->request_body_json[ $key ];
	}

	/**
	 * リクエストボディから連想配列の値を取得します。
	 *
	 * @param string $key
	 * @return array
	 */
	public function getMap( string $key ): array {
		if ( ! isset( $this->request_body_json[ $key ] ) ) {
			$this->throwNotSetError( $key );
		}
		if ( is_array( $this->request_body_json[ $key ] ) ||
			is_object( $this->request_body_json[ $key ] )
		) {
			$result = (array) $this->request_body_json[ $key ];
			// jsonのキーは文字列であるため、文字列以外が含まれている場合はエラー
			foreach ( $result as $k => $_ ) {
				if ( ! is_string( $k ) ) {
					$this->throwTypeError( $key, 'map' );
				}
			}
			return $result;
		}

		$this->throwTypeError( $key, 'map' );
	}

	private function throwNotSetError( string $key ) {
		Logger::error( "{$key} is not set." );
		Logger::error( json_encode( $this->request_body_json ) );
		throw new \Exception( '{FDD6962B-2535-49E0-89DB-A0019C05AA06}' );
	}

	private function throwTypeError( string $key, string $type ) {
		Logger::error( "{$key} is not {$type}." );
		Logger::error( json_encode( $this->request_body_json ) );
		throw new \Exception( '{DB343079-9F6E-4F77-BBA6-E2A1E513AFCF}' );
	}
}
