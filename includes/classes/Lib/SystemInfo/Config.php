<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\SystemInfo;

use Cornix\Serendipity\Core\Lib\Path\ProjectFile;

/**
 * 本プラグインで使用する構成情報等を取得するクラス。
 *
 * 画面(ブラウザ)操作によってユーザーが値を書き換えられる場合は、`Settings`クラスを使用してください。
 * インストールされる環境によって取得される値が変わる場合は、`Environment`クラスを使用してください。
 * 例えば、jsonファイルから取得するような場合は、この`Config`クラスを使用します。
 */
class Config {

	public function __construct( JsonLoader $json_loader = null ) {
		$this->json_loader = $json_loader;
	}
	/** @var JsonLoader */
	private $json_loader;

	/**
	 * 定数が記述されたjsonファイルから値を取得します。
	 * <code>
	 * ( new Config() )->getConstant( 'path.to.value' );
	 * // jsonファイルの内容が以下の場合、"foo"が返ります。
	 * // {
	 * //     "path": {
	 * //         "to": {
	 * //             "value": "foo"
	 * //         }
	 * //     }
	 * // }
	 * </code>
	 *
	 * @param string $path jsonファイル内のデータにアクセスするためのパス。ドット区切りで指定します。
	 * @return string|int|array
	 */
	public function getConstant( string $path ) {
		if ( is_null( $this->json_loader ) ) {
			$this->json_loader = new JsonLoader( ( new ProjectFile( 'includes/assets/constants.json' ) )->toLocalPath() );
		}
		return $this->json_loader->get( $path );
	}
}

/**
 * JSONファイルから値を取得するクラス。
 *
 * @internal
 */
class JsonLoader {
	public function __construct( string $json_file_path ) {
		// ファイルの内容をオブジェクトに変換して保持
		$this->data = json_decode( file_get_contents( $json_file_path ), true );
	}
	private $data;

	/**
	 * @return string|int|array
	 */
	public function get( string $path ) {
		$keys   = explode( '.', $path );
		$target = $this->data;
		foreach ( $keys as $key ) {
			assert( array_key_exists( $key, $target ) );
			$target = $target[ $key ];
		}
		return $target;
	}
}
