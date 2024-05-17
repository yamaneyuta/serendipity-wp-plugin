<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\RestApi;

use Cornix\Serendipity\Core\Access\Access;
use Cornix\Serendipity\Core\Access\Nonce;
use Cornix\Serendipity\Core\Env\Env;
use Cornix\Serendipity\Core\Utils\Strings;
use Cornix\Serendipity\Core\Utils\Url;

class DevelopmentApi extends ApiBase {

	public function __construct() {

		// 開発モードの場合のみ、APIを登録
		if ( Env::isDevelopmentMode() ) {
			// デバッグ用のAPIを登録
			add_action( 'rest_api_init', array( $this, 'add_action_rest_api_init' ) );
		}
	}

	public function add_action_rest_api_init(): void {
		$is_local_access_callback    = function () {
			return Strings::starts_with( $_SERVER['HTTP_HOST'], 'localhost' );
		};
		$default_permission_callback = function () use ( $is_local_access_callback ) {
			// 管理者かつ、localhostからのアクセスのみ許可
			return Access::isAdminUser() && Access::isAdminReferer() && Nonce::verifyAdminNonce() && $is_local_access_callback();
		};

		/**
		 * テスト用のAPIを登録
		 */
		$this->registerRestRoute(
			'/test',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					return array(
						'Url' => Url::get( 'test' ),
					);
				},
				// APIを直接叩く場合はユーザーのチェックができないため、localhostからのアクセスのみチェック
				'permission_callback' => $is_local_access_callback,
			)
		);
	}
}
