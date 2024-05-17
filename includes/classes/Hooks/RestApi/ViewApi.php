<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\RestApi;

use Cornix\Serendipity\Core\Access\Access;
use Cornix\Serendipity\Core\Access\Nonce;
use Cornix\Serendipity\Core\Content\Content;
use Cornix\Serendipity\Core\Content\PaidContent;
use Cornix\Serendipity\Core\Currency\Price\Price;
use Cornix\Serendipity\Core\Currency\Rate\CachedOracleRate;
use Cornix\Serendipity\Core\Currency\Symbol\Symbol;
use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Error\Api\NotConfirmedPurchaseError;
use Cornix\Serendipity\Core\Helpers\SafePropertyReader;
use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Settings\Settings;
use Cornix\Serendipity\Core\Utils\BNConvert;
use Cornix\Serendipity\Core\Utils\Calculator;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\TypeValidator;
use Cornix\Serendipity\Core\Utils\Ulid;
use Cornix\Serendipity\Core\Web3\CachedContract;
use Cornix\Serendipity\Core\Web3\Contract;
use Cornix\Serendipity\Core\Web3\Signer;

class ViewApi extends ApiBase {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_action_rest_api_init' ) );
	}


	public function add_action_rest_api_init(): void {

		$default_permission_callback = function ( $request ) {
			// nonceを使った正常なアクセスであること。
			// - サイトからのアクセス
			// - nonceが有効
			return Access::isSiteReferer() && Nonce::verifyViewNonce();
		};

		/**
		 * 記事閲覧時の情報を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/viewing-info',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					// 投稿IDを取得
					$post_ID = url_to_postid( wp_get_referer() );

					// 投稿IDが取得できない場合や、未公開の投稿を編集可能なユーザー以外がアクセスしようとした場合はエラー
					if ( $post_ID === 0 || ( ! Access::isEditableUser( $post_ID ) && get_post_status( $post_ID ) !== 'publish' ) ) {
						Logger::error( '[EFF73AEA] post_ID: ' . $post_ID . ', isEditableUser: ' . var_export( Access::isEditableUser( $post_ID ), true ) );
						throw new \Exception( '{816951C8-320A-44AA-95BD-6C7BE604FDC4}' );
					}

					// 有料記事部分を取得
					$paid_content = new PaidContent( $post_ID );
					$content = new Content( $post_ID );

					// 投稿IDに対する設定を取得
					$post_setting = Database::getPostSetting( $post_ID );
					if ( null === $post_setting ) {
						throw new \Exception( '{68EA01B2-D355-4AF3-9D51-B25BC87897DB}' );
					}

					// アフィリエイト報酬割合を計算
					$affiliate_ratio_hex = Calculator::percentToRatio(
						$post_setting->affiliate_percent_amount_hex,
						$post_setting->affiliate_percent_decimals,
						Constants::get( 'contract.affiliateRatioDecimals' )
					);

					// 署名用アドレス一覧を取得
					$signer_addresses = Database::getSignerAddresses();

					// 現在の署名用アドレスを取得
					$primary_signer_address_hex = Database::getPrimarySignerAddress();

					return array(
						'post_ID'                    => $post_ID,          // 投稿ID
						'selling_amount_hex'         => $post_setting->selling_amount_hex,
						'selling_decimals'           => $post_setting->selling_decimals,
						'selling_symbol'             => $post_setting->selling_symbol,
						'all_payable_symbols'        => Settings::getAllPayableSymbols(),
						'primary_signer_address_hex' => $primary_signer_address_hex, // 現在の署名用アドレス
						'signer_addresses'           => $signer_addresses, // 署名用アドレス一覧(ゲストユーザーが購入済みか確認するため、無効化された署名用アドレスも含める)
						'affiliate_ratio_hex'        => $affiliate_ratio_hex,    // アフィリエイト報酬割合
						'post_title'                 => get_the_title( $post_ID ),    // タイトル
						'post_thumbnail_url'         => $content->getThumbnailUrl(),    // サムネイル画像のURL
						'paid_characters_num'        => $paid_content->getCharactersNum(),  // 有料記事の文字数
						'paid_images_num'            => $paid_content->getImageTagsNum(),       // 有料記事部分に含まれる画像の数
					);
				},
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * トランザクションのブロック承認数を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/tx-confirmations/(?P<chain_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$chain_id = (int) $request->get_param( 'chain_id' );

					// ブロックの待機数(設定されていない場合は既定値)を取得して返す
					$default_confirmations = (int) Constants::get( "default.confirmations.$chain_id" ); // 既定値
					$confirmations = Database::getTxConfirmations_old( $chain_id, $default_confirmations );

					if ( ! is_int( $confirmations ) || $confirmations < 1 ) {
						throw new \Exception( '{835F9F58-8876-44B8-90BA-11DC03AD454F}' );
					}

					return array(
						'value' => $confirmations,
					);
				},
				'args'                => array(
					'chain_id' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							return TypeValidator::isChainId( (int) $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 購入用の署名を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/purchase-info/(?P<post_id>\d+)/(?P<chain_id>\d+)/(?P<symbol>[A-Z]+)',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					// 購入対象のチェーンID
					$chain_id = (int) $request->get_param( 'chain_id' );
					// 購入対象の投稿ID
					$post_id = (int) $request->get_param( 'post_id' );
					// 購入時に使用する通貨のシンボル
					$to_symbol = (string) $request->get_param( 'symbol' );

					// 販売者報酬を受け取るアドレスを計算するため、サイト所有者の同意情報を取得
					$site_owner_agreed_terms = Database::getSiteOwnerAgreedTermsInfo();

					if ( null === $site_owner_agreed_terms ) {
						// サイト所有者の同意がない場合は、このAPIを呼べないように画面で制御されているはず
						Logger::error( 'site_owner_agreed_terms is null' );
						throw new \Exception( '{D274CEDF-97C7-4DB9-BCA9-C86AF1ED2AD6}' );
					}
					// サイト所有者の同意時に使用したメッセージ及びそれに対する署名を取得
					$agreed_message       = $site_owner_agreed_terms['message'];
					$agreed_signature_hex = $site_owner_agreed_terms['signature'];
					// メッセージ及び署名からサイト所有者のウォレットアドレスを取得
					$to = Signer::getRecoverAccountId( $agreed_message, $agreed_signature_hex );
					if ( is_null( $to ) || 0 === strlen( $to ) ) {
						Logger::error( 'to is null or empty' );
						throw new \Exception( '{52CF77AF-0E16-4F01-9187-DFFF4765F887}' );
					}

					// 投稿IDに対する設定を取得
					$post_setting = Database::getPostSetting( $post_id );
					if ( null === $post_setting ) {
						// 投稿IDに対する設定が取得できない場合はエラー
						Logger::error( "post_id: $post_id" );
						throw new \Exception( '{D274CEDF-97C7-4DB9-BCA9-C86AF1ED2AD6}' );
					}
					if ( $post_setting->selling_paused ) {
						// 販売が一時停止されている場合はエラー
						Logger::error( "post_id: $post_id" );
						throw new \Exception( '{861183AC-0B27-4FAD-AF34-55F7D9B78EFA}' );
					}
					// チェーンでの販売が許可されていない場合はエラー
					// TODO: 実装

					// 購入可能な通貨でない場合はエラー
					if ( ! in_array( $to_symbol, Database::getPayableSymbols( $chain_id ), true ) ) {
						Logger::error( "chain_id: $chain_id, to_symbol: $to_symbol" );
						throw new \Exception( '{5CF6B2D1-1215-4F1A-B2F8-2DFD93CC1CE7}' );
					}

					// 販売価格(変換元)を取得
					$from_display_amount_hex = $post_setting->selling_amount_hex;   // 画面で入力した状態の数量
					$from_display_decimals   = $post_setting->selling_decimals; // 画面で入力した状態の小数点以下桁数
					$from_symbol             = $post_setting->selling_symbol;

					// 変換先の通貨の実際の小数点以下桁数を取得
					$contract = new CachedContract( $chain_id );

					$symbol_info = new Symbol( $contract );
					$rate = new CachedOracleRate( $contract, $from_symbol, $to_symbol );

					$amount_hex = Price::convert( $symbol_info, $rate, $from_symbol, $from_display_amount_hex, $from_display_decimals, $to_symbol );

					// チケットIDの発行、有効期限の設定、受け取り先のアドレスの設定
					$ticket_id_hex = '0x' . ( new Ulid() )->toHex();

					// アフィリエイト報酬率を計算
					$affiliate_ratio_hex = Calculator::percentToRatio(
						$post_setting->affiliate_percent_amount_hex,
						$post_setting->affiliate_percent_decimals,
						Constants::get( 'contract.affiliateRatioDecimals' )
					);

					// 署名用のメッセージを作成(※コントラクトと同じ処理になるようにすること)
					$message = BNConvert::toSolHex( $chain_id )
						. BNConvert::toSolHex( $ticket_id_hex )
						. BNConvert::toSolHex( $to )
						. BNConvert::toSolHex( $amount_hex )
						. $to_symbol
						. BNConvert::toSolHex( $post_id )
						. BNConvert::toSolHex( $affiliate_ratio_hex );

					$signer = $this->getSigner();

					$result = array(
						'post_id'          => $post_id,
						'chain_id'         => $chain_id,
						'symbol'           => $to_symbol,
						'signature_hex'    => $signer->sign( $message ),
						'ticket_id_hex'    => $ticket_id_hex,
						'to_message'       => $agreed_message,
						'to_signature_hex' => $agreed_signature_hex,
						'amount_hex'       => $amount_hex,
					);

					// DBにチケットの情報を保存
					Database::setTicketHistory( $ticket_id_hex, $post_setting->hist_set_post_id, $to_symbol, $symbol_info->getDecimals( $to_symbol ) );

					return $result;
				},
				'args'                => array(
					'post_id'  => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							$post_id = (int) $param;
							return TypeValidator::isPostId( $post_id ) && Access::isPostViewable( $post_id );
						},
					),
					'chain_id' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							// チェーンIDは数値型であること
							return TypeValidator::isChainId( (int) $param );
						},
					),
					'symbol'   => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							// ここでは文字列であることだけ確認。支払可能な通貨であるかどうかはcallbackの中でチェックする。
							return is_string( $param );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);

		/**
		 * 有料記事部分を取得するAPIを登録します。
		 */
		$this->registerRestRoute(
			'/purchased-content/(?P<post_id>\d+)',  // TODO paid-contentに変更
			array(
				'methods'             => 'POST',
				'callback'            => function ( $request ) {
					$post_id = (int) $request->get_param( 'post_id' );

					// bodyのパラメータを取得
					$request_body = new SafePropertyReader( $request->get_json_params() );
					$chain_id = $request_body->getInt( 'chainId' );
					$message = $request_body->getString( 'message' );
					$signature = $request_body->getHex( 'signature' );

					// messageの内容を取得
					$message_data = new SafePropertyReader( json_decode( $message, true ) );
					$signer = $message_data->getHex( 'signer' );
					// メッセージの中のsignerをチェックする
					// ⇒ サイト`E`の所有者`Eve`が購入者`Alice`の署名を入手し、`Eve`が別のサイト`X`で`Alice`の署名を使うことを防ぐ。
					if ( false === Database::isSignerExists( $signer ) ) {
						Logger::error( 'signer not exists: ' . $signer );
						return new NotConfirmedPurchaseError( '{181BE455-368B-4BBD-9F9E-C1C559896567}' );
					}

					$contract = new Contract( $chain_id );
					// 署名を使ってウォレットのアカウントを取得
					$account = Signer::getRecoverAccountId( $message, $signature );

					// 購入時の情報を取得
					$purchased_info = $contract->getPurchasedInfo( Database::getSignerAddresses(), $post_id, $account );
					$is_purchased = $purchased_info['isPurchased'];

					// 未購入の場合は確認エラーを返す
					if ( ! $is_purchased ) {
						Logger::warn( "Not purchased. chain_id: {$chain_id}, post_id: {$post_id}, account: {$account}" );
						return new NotConfirmedPurchaseError( '{E542D1F2-FE57-4A2C-B83A-BEE3208DB163}' );
					}
					// TODO: 購入時のsignerとブロック番号の組み合わせに問題がないかチェック
					// contract->getPurchaseEventLogを使って待機ブロック数取得し、その中に購入者のログが含まれていない場合は待機完了として扱う方向で実装

					// 有料記事を取得
					$paid_content = ( new PaidContent( $post_id ) )->getContent();
					// 有料記事のCRC32ハッシュ値を取得
					$crc32 = hash( 'crc32', $paid_content );

					return array(
						'crc32'        => $crc32,
						'paid_content' => $paid_content,
					);
				},
				'args'                => array(
					'post_id' => array(
						'required',
						'validate_callback' => function ( $param, $request, $key ) {
							$post_id = (int) $param;
							return TypeValidator::isPostId( $post_id ) && Access::isPostViewable( $post_id );
						},
					),
				),
				'permission_callback' => $default_permission_callback,
			)
		);
	}

	/**
	 * 登録されているプライベートキーを用いてSignerクラスのインスタンスを取得します。
	 */
	private function getSigner(): Signer {
		return Signer::fromPrivateKey( Database::getPrimarySignerPrivateKey() );
	}
}
