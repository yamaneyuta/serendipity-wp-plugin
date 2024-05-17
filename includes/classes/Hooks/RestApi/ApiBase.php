<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\RestApi;

use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\Constants;

abstract class ApiBase {

	const REST_API_CURRENT_VERSION = 2;

	/**
	 * バージョンを含まない、REST APIの名前空間を返します。
	 *
	 * @return string APIの名前空間 (例: `project-sample`)
	 */
	protected function getNamespaceCore(): string {
		return Constants::get( 'restNamespaceCore' );
	}


	/**
	 * 現在のバージョンの名前空間を返します。
	 *
	 * @return string APIの名前空間 (例: `project-sample/v2`)
	 */
	protected function getCurrentVersionNamespace(): string {
		return $this->getNamespaceCore() . '/v' . self::REST_API_CURRENT_VERSION;
	}


	/**
	 * このクラスで使用するREST APIの名前空間を返します。
	 * 名前空間を変更する場合は、このクラスを継承したクラスで、このメソッドをオーバーライドしてください。
	 *
	 * @return string
	 */
	protected function getNamespace(): string {
		return $this->getCurrentVersionNamespace();
	}


	/**
	 * REST APIを登録します。
	 *
	 * @param string $route REST APIのルート
	 * @param array  $args REST APIの設定
	 */
	protected function registerRestRoute( string $route, array $args = array() ): void {

		// argsの`permission_callback`が設定されていない場合は例外を投げる
		if ( ! isset( $args['permission_callback'] ) ) {
			throw new \Exception( '{8547C43F-7627-47C7-9F13-1CD29D8610E0}' );
		}

		// callbackでエラーが発生した場合はログを出力する
		$callback         = $args['callback'];
		$args['callback'] = function ( $request ) use ( $callback ) {
			try {
				return $callback( $request );
			} catch ( \Exception $e ) {
				// エラーをログに記録
				Logger::error( $e );

				// クライアント側で詳細なエラーが表示されないように汎用的なエラーメッセージを返す。
				return new \WP_Error( 'internal_server_error', 'Internal Server Error', array( 'status' => 500 ) );
			}
		};

		// APIを登録
		$registered = register_rest_route(
			$this->getNamespace(),
			$route,
			$args,
		);

		// APIの登録に失敗した場合はエラーを出力
		if ( false === $registered ) {
			Logger::error( 'API registration failed. - route: ' . $route );
			// ここでは例外を送出しない
		}
	}
}
