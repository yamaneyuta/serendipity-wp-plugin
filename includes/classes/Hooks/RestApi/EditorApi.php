<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\RestApi;

use Cornix\Serendipity\Core\Access\Access;
use Cornix\Serendipity\Core\Access\Nonce;
use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Helpers\SafePropertyReader;
use Cornix\Serendipity\Core\Utils\TypeValidator;
use Cornix\Serendipity\Core\Web3\CachedContract;

class EditorApi extends ApiBase {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_action_rest_api_init' ) );
	}


	public function add_action_rest_api_init(): void {

		$default_url_parameter_check_args = array(
			'post_id' => array(
				'required',
				'validate_callback' => function ( $param, $request, $key ) {
					return TypeValidator::isPostId( (int) $param );
				},
			),
		);

		$default_permission_callback = function ( $request ) {
			// 投稿IDを取得
			$post_id = (int) $request->get_param( 'post_id' );
			// 正常な投稿IDかつ、アクセス権限を持っていること
			return TypeValidator::isPostId( $post_id ) && Access::isEditableUser( $post_id ) && Access::isAdminReferer() && Nonce::verifyEditableUserNonce();
		};

		// 販売価格として設定可能な通貨一覧を取得するAPI
		$this->registerRestRoute(
			'/post-sellable-symbols-info/(?P<post_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {

					$price_conversion_chain_id = Database::getPriceConversionChainId();
					$contract = new CachedContract( $price_conversion_chain_id );
					$sellable_symbols_info = $contract->getSellableSymbolsInfo();

					$result = array();
					foreach ( $sellable_symbols_info['symbols'] as $i => $symbol ) {
						$is_paused = $sellable_symbols_info['isPausedSymbols'][ $i ];
						$result[] = array(
							'symbol'   => $symbol,
							'isPaused' => $is_paused,
						);
					}

					return array(
						'value' => $result,
					);
				},
				'args'                => $default_url_parameter_check_args,
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 投稿設定を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/post-setting/(?P<post_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$post_id = (int) $request->get_param( 'post_id' );

					// データを取得して返す
					$post_setting = Database::getPostSetting( $post_id );

					$is_setting_exists = $post_setting !== null;
					$selling_paused = $post_setting ? $post_setting->selling_paused : false;
					$selling_amount_hex = $post_setting ? $post_setting->selling_amount_hex : '0x0';
					$selling_decimals = $post_setting ? $post_setting->selling_decimals : 0;
					$selling_symbol = $post_setting ? $post_setting->selling_symbol : null;
					$affiliate_percent_amount_hex = $post_setting ? $post_setting->affiliate_percent_amount_hex : '0x0';
					$affiliate_percent_decimals = $post_setting ? $post_setting->affiliate_percent_decimals : 0;

					return array(
						'is_setting_exists'            => $is_setting_exists,
						'selling_paused'               => $selling_paused,
						'selling_amount_hex'           => $selling_amount_hex,
						'selling_decimals'             => $selling_decimals,
						'selling_symbol'               => $selling_symbol,
						'affiliate_percent_amount_hex' => $affiliate_percent_amount_hex,
						'affiliate_percent_decimals'   => $affiliate_percent_decimals,
					);
				},
				'args'                => $default_url_parameter_check_args,
				'permission_callback' => $default_permission_callback,
			)
		);

		// 投稿設定を行うAPI
		$this->registerRestRoute(
			'/post-setting/(?P<post_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					$post_id = (int) $request->get_param( 'post_id' );

					// bodyから値を取得するためのオブジェクトを作成
					$request_body = new SafePropertyReader( $request->get_json_params() );

					// データを更新
					Database::setPostSetting(
						$post_id,
						$request_body->getBool( 'selling_paused' ),
						$request_body->getHex( 'selling_amount_hex' ),
						$request_body->getInt( 'selling_decimals' ),
						$request_body->getString( 'selling_symbol' ),
						$request_body->getHex( 'affiliate_percent_amount_hex' ),
						$request_body->getInt( 'affiliate_percent_decimals' )
					);
				},
				'args'                => $default_url_parameter_check_args,
				'permission_callback' => $default_permission_callback,
			)
		);
	}
}
