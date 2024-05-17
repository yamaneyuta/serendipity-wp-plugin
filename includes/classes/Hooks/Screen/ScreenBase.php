<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Screen;

use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\Url;

class ScreenBase {

	/**
	 * @return string[]
	 */
	private static function getApiRootPaths(): array {
		// パーマリンク構造が基本の場合は、`/wp-json/`を含むURLではアクセスできないので`?rest_route=`を含むURLでAPIアクセスを行う。
		// 参考: https://labor.ewigleere.net/2021/11/06/wordpress-restapi-404notfound-permalink-basic/
		$is_default_permalink = get_option( 'permalink_structure' ) === '';
		$api_root_paths       = $is_default_permalink ? array( '/index.php?rest_route=/', '/wp-json/' ) : array( '/wp-json/', '/index.php?rest_route=/' );
		return $api_root_paths;
	}


	public static function addInlineCommonPhpVar( string $handle ): void {

		// javascript側で使用する変数を取得する。
		$php_block_var = array(
			'site_address'   => Url::getSiteAddress(),
			'api_root_paths' => self::getApiRootPaths(),
		);

		// プラグインのスクリプトの前に読みこまれるように登録。
		// ⇒第一引数に、wp_enqueue_scriptで登録したスクリプトのハンドルを、第三引数に'before'を指定することで実現。
		// 第二引数はインライン展開するjavascriptのコード。
		wp_add_inline_script(
			$handle,    // wp_enqueue_scriptで登録したブロッ用スクリプトのハンドルを指定。
			'var ' . Constants::get( 'phpVarName.common' ) . ' = ' . wp_json_encode( $php_block_var ) . ';', // 展開するjavascriptのコード。
			'before',   // プラグインのスクリプトの前に展開させるために指定。
		);
	}
}
