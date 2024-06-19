<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\ExportToJS;

use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use Cornix\Serendipity\Core\Lib\SystemInfo\Config;
use Cornix\Serendipity\Core\Lib\SystemInfo\Environment;

class RestVer {
	/**
	 * @param string $handle インラインスクリプトを追加するスクリプトハンドル名
	 */
	public function exportToJS( string $handle ): void {
		// javascriptとして出力する際の変数名を取得
		$js_var_name = ( new Config() )->getConstant( 'phpVarName.rest' );

		// 出力する変数の値
		$var = ( new RestVarData() )->get();

		$success = wp_add_inline_script(
			$handle,
			"var ${js_var_name} = " . wp_json_encode( $var ) . ';',
			'before',   // スクリプトの前に追加
		);

		assert( $success );
	}
}


class RestVarData {

	public function get() {
		// REST APIアクセス用のnonce
		$wp_rest_nonce = wp_create_nonce( 'wp_rest' );

		// GraphQL APIのURL
		$graphql_url = ( new Environment() )->restRootUrl() . ( new RestProperty() )->graphQLRoute();

		return array(
			'wpRestNonce' => $wp_rest_nonce,
			'graphqlUrl'  => $graphql_url,
		);
	}
}
