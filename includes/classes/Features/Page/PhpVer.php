<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\Page;

use Cornix\Serendipity\Core\Repository\Name\PhpVarName;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;

class PhpVer {
	/**
	 * @param string $handle インラインスクリプトを追加するスクリプトハンドル名
	 */
	public function addInlineScript( string $handle ): void {
		// javascriptとして出力する際の変数名を取得
		$js_var_name = ( new PhpVarName() )->get();

		// 出力する変数の値
		$var = ( new PhpVarData() )->get();

		$success = wp_add_inline_script(
			$handle,
			"var $js_var_name = " . wp_json_encode( $var ) . ';',
			'before',   // スクリプトの前に追加
		);

		assert( $success );
	}
}


class PhpVarData {

	public function get() {
		// REST APIアクセス用のnonce
		$wp_rest_nonce = wp_create_nonce( 'wp_rest' );

		// GraphQL APIのURL
		$graphql_url = ( new RestProperty() )->graphQlURL();

		// 出力する変数
		$result = array(
			'wpRestNonce' => $wp_rest_nonce,
			'graphqlUrl'  => $graphql_url,
		);

		// 現在の投稿IDが取得できる場合は追加
		$post_id = get_the_ID();
		if ( false !== $post_id ) {
			$result['postID'] = $post_id;
		}

		return $result;
	}
}
