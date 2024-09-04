<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

class BlockName {

	/** ブロックエディタで使用されるブロック名(キャッシュ) */
	private static ?string $block_name = null;

	/** ブロックエディタで使用されるブロック名を取得します。 */
	public static function get(): string {

		if ( is_null( self::$block_name ) ) {
			// /workspaces/build/block/block.json のnameを取得して保持
			$block_json       = file_get_contents( __DIR__ . '/../../../../build/block/block.json' );
			$block            = json_decode( $block_json, true );
			self::$block_name = $block['name'];
		}

		assert( ! is_null( self::$block_name ) );
		return self::$block_name;
	}
}
