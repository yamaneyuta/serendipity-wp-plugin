<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\RestApi;

use Cornix\Serendipity\Core\Access\Access;

/**
 * バージョンを含まないREST APIを登録するクラス
 */
class NoVersionApi extends ApiBase {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_action_rest_api_init' ) );
	}

	public function add_action_rest_api_init(): void {

		$default_permission_callback = function () {
			// このクラスでは、リファラのみチェックする
			// (nonceのチェック無し)
			return Access::isSiteReferer();
		};

		/**
		 * APIのバージョンを取得するAPIを登録
		 */
		$this->registerRestRoute(
			'/version',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					return array(
						'version' => self::REST_API_CURRENT_VERSION,
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);
	}


	/**
	 * {@inheritDoc}
	 */
	protected function getNamespace(): string {
		// このクラスでは、バージョンを含まない名前空間を返す
		return $this->getNamespaceCore();
	}
}
