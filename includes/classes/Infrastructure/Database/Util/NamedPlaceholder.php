<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Util;

use InvalidArgumentException;
use wpdb;

class NamedPlaceholder {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	/**
	 * Named placeholder を使用して SQL クエリを構築します
	 * ※プレースホルダは、キーがコロンで始まる形式（例: `:key`）で指定してください。
	 *
	 * @param string               $query
	 * @param array<string,string> $args プレースホルダに対応する値の連想配列
	 */
	public function prepare( string $query, array $args ): string {

		$searches = array(); // str_replace の検索用配列
		$replaces = array(); // str_replace の置換用配列

		foreach ( $args as $placeholder => $value ) {
			// プレースホルダのフォーマットとクエリに含まれているかをチェック
			$this->checkPlaceholderFormat( $placeholder );
			$this->checkPlaceholderContained( $query, $placeholder );

			// 変換元と変換後の配列に追加
			$searches[] = $placeholder;
			$replaces[] = $this->sanitizeValue( $value );
		}

		return str_replace( $searches, $replaces, $query ); // プレースホルダを置換したクエリを返す
	}


	private function sanitizeValue( $value ): string {
		if ( is_null( $value ) ) {
			return 'NULL';
		} elseif ( is_int( $value ) || is_bool( $value ) ) {
			return $this->wpdb->prepare( '%d', $value );
		} elseif ( is_float( $value ) ) {
			return $this->wpdb->prepare( '%f', $value );
		} elseif ( is_string( $value ) ) {
			return $this->wpdb->prepare( '%s', $value );
		} else {
			throw new InvalidArgumentException( '[D8496A28] Unsupported value type: ' . gettype( $value ) );
		}
	}

	/** プレースホルダが含まれているかどうかを返します */
	private function isPlaceholderContained( string $query, string $placeholder ): bool {
		return false !== strpos( $query, $placeholder );
	}
	/** プレースホルダがクエリに含まれていない場合に例外をスローします */
	private function checkPlaceholderContained( string $query, string $placeholder ): void {
		if ( ! $this->isPlaceholderContained( $query, $placeholder ) ) {
			throw new InvalidArgumentException( "[D3190731] Placeholder not found in query: {$placeholder}" );
		}
	}

	/** プレースホルダのフォーマットに一致しているかどうかを返します */
	private function isPlaceholderFormat( string $key ): bool {
		return 1 === preg_match( '/^:[a-z_]+$/', $key ); // 例: :key, :another_key など
	}
	/** プレースホルダのフォーマットが不正な場合に例外をスローします */
	private function checkPlaceholderFormat( string $key ): void {
		if ( ! $this->isPlaceholderFormat( $key ) ) {
			throw new InvalidArgumentException( "[B7A704BB] Invalid placeholder format: {$key}" );
		}
	}
}
