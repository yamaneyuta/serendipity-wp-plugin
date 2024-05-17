<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\RestApi;

use Cornix\Serendipity\Core\Access\Access;
use Cornix\Serendipity\Core\Access\Nonce;
use Cornix\Serendipity\Core\Helpers\SafePropertyReader;
use Cornix\Serendipity\Core\Utils\LocalPath;

class EveryoneApi extends ApiBase {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_action_rest_api_init' ) );
	}


	public function add_action_rest_api_init(): void {

		$default_permission_callback = function () {
			return Access::isAdminReferer() || Access::isSiteReferer();
		};

		$this->registerRestRoute(
			'/docs/terms',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {

					// 利用規約のファイルパスを取得
					$terms_file_path = LocalPath::getTermsHtmlFilePath();
					// ファイルの内容を取得
					$html = file_get_contents( $terms_file_path );

					// 取得したHTMLテキストを出力
					header( 'Content-Type: text/html' );
					// HTMLはサニタイズ済みだが、念のためここでも許可するタグを指定
					echo wp_kses(
						$html,
						array(
							'style' => array(),
							'h1'    => array(),
							'h2'    => array(),
							'p'     => array(),
						)
					) . "\n";
					// HTMLのハッシュ値を取得して出力
					$sha1 = sha1_file( $terms_file_path );
					echo "<!-- sha1: $sha1 -->\n";
					exit();
				},
				'permission_callback' => $default_permission_callback,
			),
		);

		$this->registerRestRoute(
			'nonce',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					// bodyから値を取得するためのオブジェクトを作成
					$request_body = new SafePropertyReader( $request->get_json_params() );

					// 戻り値。viewは必ず入るので初期化時に代入しておく。
					$result = array(
						'view_nonce' => Nonce::createViewActionNonce(),
					);

					// 管理画面からのアクセスかつ、投稿IDが取得できる(=投稿編集画面)場合はその投稿に対する編集権限を確認し、
					// 投稿を編集する権限がある場合は投稿編集者用のnonceも返す。
					$post_id = $request_body->getIntOrNull( 'post_id' );
					if ( Access::isAdminReferer() && ! is_null( $post_id ) ) {  // TODO: post_idのチェック方法変更
						if ( Access::isEditableUser( $post_id ) ) {
							$result['editor_nonce'] = Nonce::createEditorActionNonce();
						}
					}

					// 管理者が管理画面からアクセスしている場合は、管理者用のnonceも返す。
					if ( Access::isAdminUser() && Access::isAdminReferer() ) {
						$result['admin_nonce'] = Nonce::createAdminActionNonce();
					}

					return $result;
				},
				'permission_callback' => $default_permission_callback,
			)
		);
	}
}
