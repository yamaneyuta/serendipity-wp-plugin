<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Utils;

class Constants {

	/**
	 * @param string $node_path
	 * @return string|int|array
	 */
	public static function get( string $node_path ) {
		if ( self::$constants === null ) {
			self::loadConstants();
		}

		$nodes = explode( '.', $node_path );
		switch ( count( $nodes ) ) {
			case 1:
				return self::$constants[ $nodes[0] ];
			case 2:
				return self::$constants[ $nodes[0] ][ $nodes[1] ];
			case 3:
				return self::$constants[ $nodes[0] ][ $nodes[1] ][ $nodes[2] ];
			case 4:
				return self::$constants[ $nodes[0] ][ $nodes[1] ][ $nodes[2] ][ $nodes[3] ];
			default:
				throw new \Exception( '{ADAB34B7-E12C-4D4B-8FB1-FCBB71784B7F}' );
		}
	}

	private static function loadConstants(): void {
		// JSONファイルを読み込む。
		$constants_json = file_get_contents( LocalPath::get( 'includes/assets/constants.json' ) );
		if ( $constants_json === false ) {
			throw new \Exception( '{7DFAE211-ACC3-4605-9F68-FC6CA0D0869E}' );
		}
		self::$constants = json_decode( $constants_json, true );
	}

	// JSONファイルを読み込んだ結果をキャッシュする。
	private static $constants = null;
}
