<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\RestApi;

use Cornix\Serendipity\Core\Access\Access;
use Cornix\Serendipity\Core\Access\Nonce;
use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Env\Env;
use Cornix\Serendipity\Core\Helpers\SafePropertyReader;
use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Settings\Settings;
use Cornix\Serendipity\Core\Tools\FontInstaller;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\TypeValidator;
use Cornix\Serendipity\Core\Utils\UrlParam;
use Cornix\Serendipity\Core\Web3\CachedContract;
use Cornix\Serendipity\Core\Web3\ChainId;
use Cornix\Serendipity\Core\Web3\Rpc;

class AdminApi extends ApiBase {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_action_rest_api_init' ) );
	}


	public function add_action_rest_api_init(): void {

		$default_permission_callback = function () {
			// 管理者のアクセスであること。
			return Access::isAdminUser() && Access::isAdminReferer() && Nonce::verifyAdminNonce();
		};

		/**
		 * 開発モードかどうかを取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/development-mode',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					return array(
						'value' => Env::isDevelopmentMode(),
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * ログレベル一覧を取得します。
		 */
		$this->registerRestRoute(
			'/log-levels',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					// 設定されているログレベル一覧を取得
					$log_levels = Database::getLogLevels();

					return array(
						// `$log_levels`は空配列のことがあるため、レスポンスが`{}`となるように`object`にキャスト
						'value' => (object) $log_levels,
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * ログレベル一覧を設定します。
		 */
		$this->registerRestRoute(
			'/log-levels',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );

					// パラメータとして送信されたログレベル一覧を取得
					/** @var array<string,string> */
					$log_levels = $request_body->getMap( 'value' );

					// ログレベル一覧を設定
					Database::setLogLevels( $log_levels );
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 現在動作中のネットワーク種別を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/settings/active-network',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					return array(
						'value' => Settings::getActiveNetworkType(),
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 現在動作中のネットワーク種別を設定するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/settings/active-network',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );

					// パラメータとして送信されたネットワーク種別を取得
					$network_type = $request_body->getStringOrNull( 'value' );

					if ( ! TypeValidator::isNetworkType( $network_type ) ) {
						// `null`や、不正な文字は設定不可
						throw new \LogicException( '{969AE1F7-4C53-43D5-BD5D-BD8102EABACA}' );
					}

					// ネットワーク種別を設定
					Database::setActiveNetworkType( $network_type );
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * RPC URLに接続し、チェーンIDを返すAPIを登録します。
		 * ※ クライアントからは接続できても、サーバーから接続できない可能性があるため、サーバーから接続できるかどうかを確認するために使用。
		 */
		$this->registerRestRoute(
			'/chain-id',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );

					// RPC URLを取得
					$rpc_url = $request_body->getString( 'rpc_url' );

					return array(
						// チェーンIDを返す
						'chain_id' => Rpc::getChainId( $rpc_url ),
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * RPC URL一覧を取得するAPIを登録します。
		 *
		 * @deprecated /rpc-urls/[network_type]で代用。→ネットワーク設定画面リファクタが必要
		 * TODO: 削除後、Settings::getAllRpcUrls をprivateに変更
		 */
		$this->registerRestRoute(
			'/rpc-urls',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					// すべてのRPC URLを取得して返す
					return array(
						'value' => (object) Settings::getAllRpcUrls(),
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * RPC URL一覧を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/rpc-urls/(?P<network_type>(\w+))',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$network_type = $request->get_param( 'network_type' );
					// 対象のネットワーク種別に属するRPC URL一覧を取得して返す
					return array(
						'value' => (object) Settings::getRpcUrls( $network_type ),
					);
				},
				'args'                => array(
					'network_type' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							return TypeValidator::isNetworkType( $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * RPC URLを更新するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/rpc-url/(?P<chain_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					$chain_id = (int) $request->get_param( 'chain_id' );

					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );

					// パラメータとして送信されたRPC URLを取得
					$rpc_url = $request_body->getStringOrNull( 'rpc_url' );

					// RPC URLが指定されている時(新規登録or更新時)
					if ( is_string( $rpc_url ) ) {
						// 念のためチェーンIDを確認
						$rpc_chain_id = Rpc::getChainId( $rpc_url );
						if ( $chain_id !== $rpc_chain_id ) {
							Logger::error( "chain_id is not match: $chain_id !== $rpc_chain_id" );
							throw new \Exception( '{66D79DE2-40D1-43B0-AF69-5257AFB9A6D1}' );
						}

						$initial_block_number_hex = Database::getInitialBlockNumber( $chain_id );
						if ( is_null( $initial_block_number_hex ) ) {
							// 初回のRPC URL登録の場合は、初期ブロック番号を取得してDBに保存
							$initial_block_number_hex = Rpc::getBlockNumber( $rpc_url );
							Database::setInitialBlockNumber( $chain_id, $initial_block_number_hex );
						}
					}

					// RPC URLの登録
					Database::setRpcUrl( $chain_id, $rpc_url );
				},
				'args'                => array(
					'chain_id' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							// チェーンIDは数値型であること
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		/** サイト所有者が利用規約に同意したときの情報を取得します。 */
		$this->registerRestRoute(
			'/site-owner-agreed-terms',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					// サイト所有者の利用規約同意情報を取得
					return array(
						'value' => Database::getSiteOwnerAgreedTermsInfo(),
					);
				},
				'permission_callback' => $default_permission_callback,
			),
		);

		$this->registerRestRoute(
			'/site-owner-agreed-terms',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );
					$version = $request_body->getStringOrNull( 'version' );
					$message = $request_body->getStringOrNull( 'message' );
					$signature = $request_body->getStringOrNull( 'signature' );

					// サイト所有者の利用規約同意情報を設定
					Database::setSiteOwnerAgreedTermsInfo( $version, $message, $signature );
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 指定したネットワークから支払可能な通貨を取得するAPIを登録します。
		 * ※ ブロックチェーンのネットワーク上で支払可能な一覧であり、サイトの設定とは関係がないことに注意
		 */
		$this->registerRestRoute(
			'/blockchain/payable-symbols-info/(?P<chain_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$chain_id = (int) $request->get_param( 'chain_id' );
					$contract = new CachedContract( $chain_id );
					// TODO: ここは最新の情報を取得する
					return $contract->getPayableSymbolsInfo( $chain_id );
				},
				'args'                => array(
					'chain_id' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							// チェーンIDは数値型であること
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 指定したネットワークでの支払可能な通貨シンボル設定を取得するAPIを登録します。
		 * 販売設定画面で、チェーンに対する支払可能通貨シンボルを設定するために使用する
		 */
		$this->registerRestRoute(
			'/settings/payable-symbols/(?P<chain_ids>\d+(\|\d+)*)',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$chain_ids = UrlParam::toChainIds( $request->get_param( 'chain_ids' ) );
					return array(
						'value' => Settings::getPayableSymbols( $chain_ids ),
					);
				},
				'args'                => array(
					'chain_ids' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							return UrlParam::isChainIdsFormat( $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 指定したチェーンで支払可能な通貨を設定するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/settings/payable-symbols/(?P<chain_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					$chain_id = (int) $request->get_param( 'chain_id' );

					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );
					// 支払可能な通貨シンボル一覧を取得
					$symbols = $request_body->getStringArray( 'value' );
					// 支払可能な通貨シンボル一覧を保存
					Database::setPayableSymbols( $chain_id, $symbols );
				},
				'args'                => array(
					'chain_id' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							// チェーンIDは数値型であること
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * フォントをインストールするAPIを登録します。
		 */
		$this->registerRestRoute(
			'/install-font',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {

					// パラメータチェック
					$file = $request->get_file_params();
					$font_file_info = isset( $file['font_file'] ) ? $file['font_file'] : null;
					if ( null === $font_file_info ) {
						// `font_file`パラメータに何も指定されていない場合
						throw new \Exception( '{3110118A-9483-4DE1-A9F8-875864CC8DA2}' );
					}

					if ( false === isset( $font_file_info['name'] ) || false === is_string( $font_file_info['name'] ) ||
					false === isset( $font_file_info['tmp_name'] ) || false === is_string( $font_file_info['tmp_name'] ) ||
					false === isset( $font_file_info['size'] ) || false === is_integer( $font_file_info['size'] )
					) {
						Logger::error( var_export( $font_file_info, true ) );
						throw new \Exception( '{9188A1AA-D744-41EF-BAAD-AF151FCB9737}' );
					}

					// パラメータ取得
					$font_file_name = (string) $font_file_info['name'];  // アップロードされたフォントのファイル名
					$tmp_font_file_path = (string) $font_file_info['tmp_name'];  // 一時ファイルのパスを取得(`/tmp/phpEilzsW`のような形で取得できる)
					$font_file_size = (int) $font_file_info['size'];  // アップロードされたフォントのファイルサイズ

					if ( '' === $tmp_font_file_path || 0 === $font_file_size ) {
						// tmp_nameが空であったり、sizeが0の場合
						// ⇒ファイルサイズの上限を超えている場合
						// TODO: エラー処理(WP_Error)
						throw new \Exception( '{DF599F61-6367-4258-878B-10C962BDAEB8}' );
					}

					// 一時領域にフォントファイルを元のファイル名で保存。
					// ⇒ 'load_font.php'内でファイル拡張子のチェックが存在するため。
					// ⇒ ファイル名がそのまま`/dompdf/dompdf/lib/fonts`フォルダに保存されるため。
					//
					// まずは一時領域にUUIDv4のフォルダを作成。その中にフォントファイルを保存。
					$font_file_dir = sys_get_temp_dir() . '/' . wp_generate_uuid4();
					if ( false === mkdir( $font_file_dir ) ) {
						Logger::error( 'mkdir failed: ' . $font_file_dir );
						throw new \Exception( '{D73A1AA1-36C6-4794-9D41-3D9AD0B53260}' );
					}
					$font_file_path = $font_file_dir . '/' . $font_file_name;
					if ( false === copy( $tmp_font_file_path, $font_file_path ) ) {
						Logger::error( 'copy failed: ' . $tmp_font_file_path . ' -> ' . $font_file_path );
						throw new \Exception( '{7B9BBFAC-49CF-4455-89BE-C84A5AD3BC22}' );
					}

					try {
						// フォントインストール
						FontInstaller::execute( $font_file_path );
					} finally {
						// 一時ファイルを削除
						unlink( $font_file_path );
						rmdir( $font_file_dir );
					}
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * ライブラリインストール時に同梱されていたフォント一覧を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/dist-installed-fonts',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					// ライブラリ同梱のフォント一覧を返す
					return array(
						'fonts' => FontInstaller::getDistInstalledFonts(),
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * ユーザーがインストールしたフォント一覧を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/user-installed-fonts',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					// ユーザーがインストールしたフォント一覧を返す
					return array(
						'fonts' => FontInstaller::getUserInstalledFonts(),
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 販売履歴を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/sales-histories',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );

					$start_unix_time = $request_body->getIntOrNull( 'start_unix_time' );
					$end_unix_time = $request_body->getIntOrNull( 'end_unix_time' );

					return array(
						'value' => Database::getSalesHistories( $start_unix_time, $end_unix_time ),
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 現在稼働中のネットワーク種別(mainnet/testnet/privatenet)における、トランザクションの待機数を取得するAPIを取得します。
		 */
		$this->registerRestRoute(
			'/active-network-type/tx-confirmations',
			array(
				'methods'             => 'GET',
				'callback'            => function () {
					$result = Settings::getTxConfirmations( Settings::getActiveNetworkType() );

					// ブロックの待機数を取得して返す
					return array(
						'value' => (object) $result,
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * ブロックの待機数を取得するAPIを登録します。
		 * ※ Viewのブロック数取得とは異なり、設定されていない場合はnullを返す。
		 *
		 * @deprecated
		 */
		$this->registerRestRoute(
			'/settings/tx-confirmations/(?P<chain_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$chain_id = (int) $request->get_param( 'chain_id' );

					// ブロックの待機数を取得して返す
					return array(
						'value'   => Database::getTxConfirmations_old( $chain_id, null ),
						'default' => (int) Constants::get( "default.confirmations.$chain_id" ),
					);
				},
				'args'                => array(
					'chain_id' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							// チェーンIDは数値型であること
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * ブロックの待機数を設定するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/settings/tx-confirmations/(?P<chain_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					$chain_id = (int) $request->get_param( 'chain_id' );

					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );

					// パラメータとして送信されたブロックの待機数を取得
					$tx_confirmations = $request_body->getIntOrNull( 'value' );

					// ブロックの待機数を設定
					Database::setTxConfirmations( $chain_id, $tx_confirmations );
				},
				'args'                => array(
					'chain_id' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							// チェーンIDは数値型であること
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		$this->registerRestRoute(
			'/debug-info',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$ini_values = array(
						// OS関連
						'memory_limit',
						'max_execution_time',
						// ファイルアップロード関連
						'post_max_size',
						'upload_max_filesize',
						'allow_url_fopen',  // OFFの場合、`file_get_contents`でエラーが発生
						// セキュリティ関連
						'disable_functions',
						'disable_classes',
					);
					$ini_results = array();
					foreach ( $ini_values as $ini_value ) {
						$ini_results[ $ini_value ] = ini_get( $ini_value );
					}

					// 主にサードパーティ製ライブラリで参照される拡張機能
					$ext_values = array(
						'bcmath',
						'gmp',
						'gmagick',
						'hash',
						'imagick',
						'libsodium',
						'mbstring',
						'mcrypt',
						'mhash',
						'openssl',
						'sodium',
						'suhosin',
						'xml',
					);
					$ext_results = array();
					foreach ( $ext_values as $ext_value ) {
						$ext_results[ $ext_value ] = extension_loaded( $ext_value );
					}

					// TODO: 作成途中。必要な情報を追加する。
					global $wp_version;
					return array(
						'user_data' => array(),
						'system'    => array(
							'wordpress' => array(
								'version'          => $wp_version,
								'WP_DEBUG'         => WP_DEBUG,
								'WP_DEBUG_LOG'     => WP_DEBUG_LOG,
								'WP_DEBUG_DISPLAY' => WP_DEBUG_DISPLAY,
							),
							'php'       => array(
								'version'    => phpversion(),
								// 実行時間関連(Windowsの場合、DBアクセス等でもカウントされてしまうのでOSの情報も取得)
								// https://www.php.net/manual/ja/info.configuration.php#ini.max-execution-time
								'PHP_OS'     => PHP_OS,
								'php_uname'  => php_uname(),

								'ini'        => $ini_results,
								'extensions' => $ext_results,
							),
						),
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);
	}
}
