<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

/**
 * RPC URLの提供者を表すクラス
 */
final class RpcUrlProviderType {

	// 各RPC URL提供者を定義。値は特に意味を持たないが、重複しないようにすること。
	private const PRIVATE = 'private';  // プライベートネットの提供者(このマシン)
	private const ANKR    = 'ankr';
	private const SONEIUM = 'soneium';

	private function __construct( string $name ) {
		$this->name = $name;
	}
	/**
	 * RPC URLの提供者の名前を保持
	 * ※ 以下の2つを行うために定義。
	 * 　　- if文等の判定で`==`が誤って記載された時にfalseとなるようにする
	 * 　　- エラーログなどに出力できるように__toStringで用いる
	 */
	private string $name;

	public function __toString(): string {
		return $this->name;
	}

	/**
	 * @var array<string,RpcUrlProviderType>
	 */
	private static array $cache = array();

	private static function from( string $name ): RpcUrlProviderType {
		if ( ! isset( self::$cache[ $name ] ) ) {
			self::$cache[ $name ] = new RpcUrlProviderType( $name );
		}
		return self::$cache[ $name ];
	}

	/**
	 * プライベートネットの提供者を表すインスタンスを取得します。
	 *
	 * @return RpcUrlProviderType
	 */
	public static function private(): RpcUrlProviderType {
		return self::from( self::PRIVATE );
	}

	/**
	 * ankrを表すインスタンスを取得します。
	 *
	 * @return RpcUrlProviderType
	 */
	public static function ankr(): RpcUrlProviderType {
		return self::from( self::ANKR );
	}

	/**
	 * Soneiumを表すインスタンスを取得します。
	 */
	public static function soneium(): RpcUrlProviderType {
		return self::from( self::SONEIUM );
	}
}
