<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\InlineScript;

use Cornix\Serendipity\Core\Lib\SystemInfo\Config;

class PhpVer {
	/**
	 *
	 * @param string $handle インラインスクリプトを追加するスクリプトハンドル名
	 */
	public function add( string $handle ): void {
		// javascriptとして出力する際の変数名を取得
		$js_var_name = ( new Config() )->getConstant( 'phpVarName.common' );

		// 出力する変数の値
		$var = array(
			'wpRestNonce' => wp_create_nonce( 'wp_rest' ),
		);

		$success = wp_add_inline_script(
			$handle,
			"var ${js_var_name} = " . wp_json_encode( $var ) . ';',
			'before',   // スクリプトの前に追加
		);

		assert( $success );
	}
}
