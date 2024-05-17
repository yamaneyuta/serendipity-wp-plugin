<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Database;

use Cornix\Serendipity\Core\Database\DataType\LogData;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Database\DataType\PostSettingData;
use Cornix\Serendipity\Core\Database\DataType\SalesHistoryData;
use Cornix\Serendipity\Core\Env\Env;
use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\TypeValidator;
use Cornix\Serendipity\Core\Web3\Signer;
use Cornix\Serendipity\Core\Utils\Ulid;
use Cornix\Serendipity\Core\Web3\DataType\PurchaseEventLogData;

/**
 * WordPressのデータベースに対しての操作を行うクラス。
 *
 * ※ key-value型のデータをoptionsテーブルに保存する場合、シリアライズ化され、キーは数値型に変換される可能性がある。
 *    -> チェーンIDを文字列型のキーとして保存しても、取得時は数値型に変換がされている。
 *    -> PHPの処理では、チェーンIDのキーは数値型として扱うことにする。
 */
class Database {

	/** キーとして使用するためのIDを生成します。 */
	private static function getNewId(): string {
		return ( new Ulid() )->toString();
	}
	private static function unixTimeToUlidObject( int $unix_time ): Ulid {
		$ulid_bytes = array();

		$time = $unix_time * 1000;
		for ( $i = 5; $i >= 0; $i-- ) {
			array_unshift( $ulid_bytes, $time & 0xff );
			$time >>= 8;
		}
		for ( $i = 0; $i < 10; $i++ ) {
			$ulid_bytes[] = 0;
		}
		return new Ulid( $ulid_bytes );
	}
	private static function unixTimeToUlidHex( int $unix_time ): string {
		return self::unixTimeToUlidObject( $unix_time )->toHex();
	}

	/**
	 * 投稿IDに紐づく設定を取得します。
	 */
	public static function getPostSetting( int $post_id ): ?PostSettingData {
		global $wpdb;

		$table_name = TableName::getHistorySettingPostTableName();

		$sql = <<<SQL
			SELECT
				hist_set_post_id,
				post_id,
				selling_paused,
				selling_amount_hex,
				selling_decimals,
				selling_symbol,
				affiliate_percent_amount_hex,
				affiliate_percent_decimals
			FROM {$table_name}
			WHERE hist_set_post_id = (
					SELECT MAX(hist_set_post_id)
					FROM {$table_name}
					WHERE post_id = %d
				)
		SQL;

		$query = $wpdb->prepare(
			$sql,
			$post_id
		);

		$row = $wpdb->get_row( $query, ARRAY_A );

		if ( null === $row ) {
			return null;
		}

		$post_setting = new PostSettingData(
			(string) $row['hist_set_post_id'],
			(int) $row['post_id'],
			(bool) $row['selling_paused'],
			(string) $row['selling_amount_hex'],
			(int) $row['selling_decimals'],
			(string) $row['selling_symbol'],
			(string) $row['affiliate_percent_amount_hex'],
			(int) $row['affiliate_percent_decimals']
		);

		return $post_setting;
	}

	/**
	 * 投稿IDに紐づく設定を保存します。
	 */
	public static function setPostSetting(
		int $post_id,
		bool $selling_paused,
		string $selling_amount_hex,
		int $selling_decimals,
		string $selling_symbol,
		string $affiliate_percent_amount_hex,
		int $affiliate_percent_decimals
	): void {
		$hist_set_post_id = self::getNewId();

		global $wpdb;

		$table_name = TableName::getHistorySettingPostTableName();

		$sql = <<<SQL
			INSERT INTO {$table_name}
			(hist_set_post_id, post_id, selling_paused, selling_amount_hex, selling_decimals, selling_symbol, affiliate_percent_amount_hex, affiliate_percent_decimals)
			VALUES (%s, %d, %d, %s, %d, %s, %s, %d)
		SQL;

		$query = $wpdb->prepare(
			$sql,
			$hist_set_post_id,
			$post_id,
			$selling_paused,
			$selling_amount_hex,
			$selling_decimals,
			$selling_symbol,
			$affiliate_percent_amount_hex,
			$affiliate_percent_decimals
		);

		$wpdb->query( $query );
	}


	public static function setTicketHistory( string $ticket_id_hex, string $hist_set_post_id, string $payment_symbol, int $payment_decimals ): void {
		global $wpdb;

		$table_name = TableName::getHistoryTicketsTableName();

		$sql = <<<SQL
			INSERT INTO {$table_name}
			(ticket_id_hex, hist_set_post_id, payment_symbol, payment_decimals)
			VALUES (%s, %s, %s, %d)
		SQL;

		$query = $wpdb->prepare(
			$sql,
			$ticket_id_hex,
			$hist_set_post_id,
			$payment_symbol,    // TODO: 削除 PurchaseEventLogから取得可能のため
			$payment_decimals
		);

		$wpdb->query( $query );

		return;
	}


	/**
	 * 販売履歴を取得します。販売履歴画面表示用。
	 *
	 * @param int|null $start_unix_time
	 * @param int|null $end_unix_time
	 * @return SalesHistoryData[]
	 */
	public static function getSalesHistories( ?int $start_unix_time, ?int $end_unix_time ): array {

		global $wpdb;

		// チケット発行時間(tickets.ticket_id_hex)で絞り込みを行うため、時間からULIDのHEXを生成
		$start_id = is_integer( $start_unix_time ) ? '0x' . self::unixTimeToUlidHex( $start_unix_time ) : null;
		$end_id   = is_integer( $end_unix_time ) ? '0x' . self::unixTimeToUlidHex( $end_unix_time ) : null;

		$purchase_events_table_name = TableName::getHistoryPurchaseEventsTableName();
		$tickets_table_name         = TableName::getHistoryTicketsTableName();
		$post_settings              = TableName::getHistorySettingPostTableName();
		$posts                      = TableName::getPostsTableName();

		$where_clause = '';
		if ( ! is_null( $start_id ) ) {
			$where_clause .= ' AND tickets.ticket_id_hex >= %s';
		}
		if ( ! is_null( $end_id ) ) {
			$where_clause .= ' AND tickets.ticket_id_hex <= %s';
		}

		$sql = <<<SQL
			SELECT
				UNIX_TIMESTAMP(tickets.created_at) AS ticket_created_at_unix,
				purchase_events.chain_id AS chain_id,
				posts.ID AS post_id,
				posts.post_title AS post_title,
				post_settings.selling_amount_hex AS selling_amount_hex,
				post_settings.selling_decimals AS selling_decimals,
				post_settings.selling_symbol AS selling_symbol,
				purchase_events.symbol AS payment_symbol,
				tickets.payment_decimals AS payment_decimals,
				purchase_events.profit_hex AS profit_amount_hex,
				purchase_events.commission_hex AS fee_amount_hex,
				purchase_events.affiliate_hex AS affiliate_amount_hex,
				purchase_events.from_hex AS from_address,
				purchase_events.to_hex AS to_address,
				purchase_events.affiliate_account_hex AS affiliate_address,
				purchase_events.transaction_hash_hex AS transaction_hash_hex
			FROM {$purchase_events_table_name} AS purchase_events
			LEFT JOIN {$tickets_table_name} AS tickets
				ON purchase_events.ticket_id_hex = tickets.ticket_id_hex
			LEFT JOIN {$post_settings} AS post_settings
				ON tickets.hist_set_post_id = post_settings.hist_set_post_id
			LEFT JOIN {$posts} AS posts
				ON post_settings.post_id = posts.id
			WHERE 1 = 1
				{$where_clause}
		SQL;

		$query = $wpdb->prepare(
			$sql,
			$start_id,
			$end_id
		);

		$rows = $wpdb->get_results( $query, ARRAY_A );

		$result = array();
		foreach ( $rows as $row ) {
			$result[] = new SalesHistoryData(
				(int) $row['ticket_created_at_unix'],
				(int) $row['chain_id'],
				(int) $row['post_id'],
				(string) $row['post_title'],
				(string) $row['selling_amount_hex'],
				(int) $row['selling_decimals'],
				(string) $row['selling_symbol'],
				(string) $row['payment_symbol'],
				(int) $row['payment_decimals'],
				(string) $row['profit_amount_hex'],
				(string) $row['fee_amount_hex'],
				(string) $row['affiliate_amount_hex'],
				(string) $row['from_address'],
				(string) $row['to_address'],
				(string) $row['affiliate_address'],
				(string) $row['transaction_hash_hex']
			);
		}

		return $result;
	}

	/**
	 * 購入イベントログを保存します。
	 *
	 * @param PurchaseEventLogData $data
	 * @return void
	 */
	public static function setPurchaseEventLog( PurchaseEventLogData $data ): void {
		$id = self::getNewId();

		global $wpdb;

		$table_name = TableName::getHistoryPurchaseEventsTableName();

		// `transaction_hash_hex`が重複しなければINSERT
		$sql = <<<SQL
			INSERT INTO {$table_name}
			(id, chain_id, block_number_hex, transaction_hash_hex, ticket_id_hex, from_hex, to_hex, symbol, profit_hex, commission_hex, affiliate_hex, affiliate_account_hex)
			SELECT %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
			WHERE NOT EXISTS (
				SELECT *
				FROM {$table_name}
				WHERE transaction_hash_hex = %s
			)
		SQL;

		$query = $wpdb->prepare(
			$sql,
			$id,
			$data->chain_id,
			$data->block_number_hex,
			$data->transaction_hash_hex,
			$data->ticket_id_hex,
			$data->from_hex,
			$data->to_hex,
			$data->symbol,
			$data->profit_hex,
			$data->commission_hex,
			$data->affiliate_hex,
			$data->affiliate_account_hex,
			$data->transaction_hash_hex
		);

		$wpdb->query( $query );

		return;
	}

	/**
	 * 購入イベントログの記録をDBから削除します。
	 *
	 * @param int[] $chain_ids 削除対象のチェーンID一覧
	 * @return int|bool 削除された行数
	 */
	public static function deletePurchaseEventLogs( array $chain_ids ) {
		global $wpdb;

		if ( false === Env::isDevelopmentMode() ) {
			// 開発モード以外での使用は想定していない
			// =>アンインストール時に使用する可能性があるため、実装時に検討
			throw new \Exception( '{A3E620FF-F3AF-41AE-96BB-ED5514A44DEE}' );
		}

		$table_name = TableName::getHistoryPurchaseEventsTableName();

		$in = implode( ',', array_fill( 0, count( $chain_ids ), '%d' ) );

		$sql = <<<SQL
			DELETE FROM {$table_name}
			WHERE chain_id IN ({$in})
		SQL;

		$query = $wpdb->prepare(
			$sql,
			...$chain_ids
		);

		return $wpdb->query( $query );
	}


	/**
	 *
	 * @param LogData[] $logs
	 * @return int|bool
	 */
	public static function log( array $logs ) {
		global $wpdb;

		$table_name = TableName::getLogsTableName();

		$pid = getmypid();
		if ( false === $pid ) {
			$pid = -1;
		}

		// バルクインサート用のデータを作成
		$bulk_insert_all_data = array();
		foreach ( $logs as $log ) {
			$bulk_insert_all_data[] = $pid;
			$bulk_insert_all_data[] = $log->log_timestamp;
			$bulk_insert_all_data[] = $log->log_level;
			$bulk_insert_all_data[] = $log->uri;
			$bulk_insert_all_data[] = $log->source;
			$bulk_insert_all_data[] = $log->log_message;
			$bulk_insert_all_data[] = $log->plugin_version;
		}

		// バルクインサートのVALUES部分を作成
		$values_str = implode( ',', array_fill( 0, count( $logs ), '(%d, %f, %s, %s, %s, %s, %s)' ) );
		$sql        = <<<SQL
			INSERT INTO {$table_name} (pid, log_timestamp, log_level, uri, source, log_message, plugin_version) VALUES {$values_str};
		SQL;

		$query = $wpdb->prepare(
			$sql,
			...$bulk_insert_all_data
		);

		return $wpdb->query( $query );
	}

	/**
	 * ログテーブルの全データを削除します。
	 *
	 * @return int|bool
	 */
	public static function deleteAllLogs(): int {
		global $wpdb;

		if ( false === Env::isDevelopmentMode() ) {
			// 開発モード以外での使用は想定していない
			throw new \Exception( '{BE2B7282-CABB-4A1C-B924-A7EA74FD0106}' );
		}

		$table_name = TableName::getLogsTableName();

		$sql = <<<SQL
			DELETE FROM {$table_name}
		SQL;

		return $wpdb->query( $sql );
	}

	// ------------------------------------------------------------------------

	// TODO: update_optionの処理を実施するupdateOption関数をprivateで宣言し、
	// 失敗時はログ出力を行うようにする。

	/**
	 * オプションテーブルへ値を保存します。
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param bool   $autoload
	 * @return bool
	 */
	private static function updateOption( string $key, $value, bool $autoload ): bool {
		$success = update_option( $key, $value, $autoload );
		if ( false === $success ) {
			Logger::error( "[05C333BC] Failed to update option. key: {$key}, value: " . json_encode( $value ) );
			throw new \Exception( '{3AC020E0-216F-4243-8352-F83F69B2C8A8}' );
		}
		return $success;
	}

	/**
	 * Nonce生成用のソルトを取得します。
	 *
	 * @return string
	 */
	public static function getNonceSalt(): string {
		// `/wp-admin/options.php`で見たときに直接表示されないようにオブジェクト(array)型で保存

		$salt = get_option( Constants::get( 'optionsKey.nonceSalt' ), array() );
		if ( ! isset( $salt['value'] ) ) {
			$salt     = array( 'value' => str_replace( '-', '', wp_generate_uuid4() ) );
			$autoload = true;   // ゲストユーザーアクセス時に使用するため、`autoload`は`true`にする。
			update_option( Constants::get( 'optionsKey.nonceSalt' ), $salt, $autoload );
			Logger::info( 'Nonce salt generated.' );
		}

		return $salt['value'];
	}

	/**
	 * ログレベル一覧を取得します。
	 *
	 * @return array<string,string>
	 * ex. { "server": "debug", "client": "info" }
	 *
	 * ユーザーによる設定がされていない場合、キーが設定されないことに注意。
	 * 以下の値が返ることがあります。
	 * ex. [], { "server": "debug" }, ...
	 */
	public static function getLogLevels(): array {
		$ret = get_option( Constants::get( 'optionsKey.logLevelSettings' ), null );
		if ( is_null( $ret ) ) {
			// 次回から存在しないキーを探さないように、空配列を設定
			$ret = array();
			self::setLogLevels( $ret );
		}
		return $ret;
	}

	/**
	 * ログレベル一覧を保存します。
	 *
	 * @param array<string,string> $log_levels
	 * @return void
	 */
	public static function setLogLevels( array $log_levels ): void {
		// 入力値チェック
		foreach ( $log_levels as $target => $log_level ) {
			if ( ! TypeValidator::isLogLevel( $log_level ) || ! TypeValidator::isLogLevelTarget( $target ) ) {
				Logger::error( "[8B547D10] target: {$target}, log_level: {$log_level}, log_levels: " . json_encode( $log_level ) );
				throw new \LogicException( '{6D744729-3807-4F1D-B767-1C6B60309439}' );
			}
		}

		$key = Constants::get( 'optionsKey.logLevelSettings' );
		// ログレベルの確認は頻繁に発生するため、`autoload`は`true`にする。
		$autoload = true;
		$success  = update_option( $key, $log_levels, $autoload );
		if ( false === $success ) {
			Logger::error( "[EE9E2E5B] key: {$key}, log_levels: " . json_encode( $log_levels ) );
			throw new \RuntimeException( '{E3A4069F-33EE-4AD8-BC87-568E7B7C17E2}' );
		}
	}

	/**
	 * ログレベルを取得します。
	 * ログレベルがoptionsテーブルに記録されていない場合は既定値をjsonファイルから取得して返します。
	 *
	 * @return string|null
	 */
	public static function getLogLevel( string $target, bool $use_default = true ): ?string {
		// ログレベル一覧を取得。
		$log_level_settings = self::getLogLevels();

		// 取得した設定に対象のログレベルが含まれている場合はその値を返す
		if ( in_array( $target, array_keys( $log_level_settings ) ) ) {
			return $log_level_settings[ $target ];
		}

		// optionsテーブルに保存されておらず、既定値を使う場合は既定値を返す
		if ( $use_default ) {
			/** @var string */
			$default = Constants::get( 'default.logLevel.' . $target );

			if ( null === $default ) {
				// Loggerクラスでこの関数呼び出しがあるため、ここでは例外を投げるのみ
				throw new \LogicException( '{2C68F16B-C9DA-4B04-8416-5F93764F0B54}' );
			}

			return $default;
		}

		return null;
	}

	/**
	 * optionsテーブルに保存されているプラグインのバージョンを取得します。
	 * ※ プラグインのアップデート判定用。現在のプラグインのバージョンとは関係がないことに注意。
	 *
	 * @return null|string
	 */
	public static function getPluginVersion(): ?string {
		return get_option( Constants::get( 'optionsKey.pluginVersion' ), null );
	}

	/**
	 * optionsテーブルにプラグインのバージョンを保存します。
	 *
	 * @param string $version
	 */
	public static function setPluginVersion( string $version ): void {
		// 管理画面でのみ使用する値のため、`autoload`は`false`にする。
		$autoload = false;
		update_option( Constants::get( 'optionsKey.pluginVersion' ), $version, $autoload );
	}


	/**
	 * このプラグインの動作ネットワーク種別を取得します。
	 *
	 * @return null|string 'mainnet' | 'testnet' | 'privatenet' | null
	 */
	public static function getActiveNetworkType(): ?string {
		return get_option( Constants::get( 'optionsKey.activeNetworkType' ), null );
	}


	/**
	 * このプラグインの動作ネットワーク種別を保存します。
	 *
	 * @param string $network_type 'mainnet' | 'testnet' | 'privatenet'
	 * @return void
	 */
	public static function setActiveNetworkType( string $network_type ): void {
		// 引数チェック
		if ( ! TypeValidator::isNetworkType( $network_type ) ) {
			Logger::error( 'Invalid network type. - ' . $network_type );
			throw new \LogicException( '{20AF186B-DD65-49B9-A828-409EE5253E1D}' );
		}

		// DBに保存
		$success = update_option( Constants::get( 'optionsKey.activeNetworkType' ), $network_type, true );
		if ( false === $success ) {
			Logger::error( 'Failed to update operation network type.' );
			throw new \RuntimeException( '{AE583A6F-EF98-4D7D-9051-9CCB660E3838}' );
		}
	}


	/**
	 * 価格変換を実施するチェーンIDを取得します。
	 */
	public static function getPriceConversionChainId(): int {

		// 動作ネットワーク種別を取得
		$network_type = self::getActiveNetworkType();

		// 動作ネットワークにおける価格変換を実施するチェーンIDを取得
		$chain_id = (int) Constants::get( "networks.$network_type.priceConversionChainId" );
		if ( ! TypeValidator::isChainId( $chain_id ) ) {
			// ここを通る場合は、JSONファイルの内容を確認する
			throw new \LogicException( '{2B210112-C61D-4A65-B87B-2607BAE0306B}' );
		}

		return $chain_id;
	}

	/**
	 * 購入可能な通貨シンボル一覧を取得します。
	 *
	 * @return array<int,string[]>
	 */
	public static function getAllPayableSymbols(): array {
		return get_option( OptionKey::payableSymbols(), array() );
	}


	/**
	 * @param int      $chain_id
	 * @param string[] $symbols
	 */
	public static function setPayableSymbols( int $chain_id, array $symbols ): void {
		/*
		format:
			{
				1: ["ETH", "USDT"],
				137: ["MATIC", "USDC", "USDT"]
			}
		*/
		$key                          = OptionKey::payableSymbols();
		$payable_symbols              = get_option( $key, array() );
		$payable_symbols[ $chain_id ] = $symbols;
		update_option( $key, $payable_symbols, true );
	}

	/**
	 * @return string[]
	 */
	public static function getPayableSymbols( int $chain_id ): array {
		/** @var array<string,string[]> */
		$payable_symbols = get_option( OptionKey::payableSymbols(), array() );
		return $payable_symbols[ $chain_id ] ?? array();
	}


	/**
	 * 現在使用中の署名用アドレスに関する情報を取得します。(optionsテーブルに存在しない場合は新規作成)
	 *
	 * @return array{private_key:string}
	 * @deprecated primary_private_keyのoptionに変更
	 */
	private static function getPrimarySignerInfo(): array {
		$key                 = Constants::get( 'optionsKey.primarySigner' );
		$primary_signer_info = get_option( $key, null );

		// 署名用アドレスが存在しない場合は新規作成して保存
		if ( null === $primary_signer_info ) {
			$signer              = Signer::create();
			$primary_signer_info = array(
				'private_key' => $signer->getPrivateKey(),
			);
			// `/wp-admin/options.php`で見たときに秘密鍵が
			// 直接表示されないようにオブジェクト(array)型で保存
			update_option( $key, $primary_signer_info, true );
		}

		return $primary_signer_info;
	}

	// /**
	// * 現在使用中の署名用アドレスが存在しない場合は新規作成します。
	// */
	// public static function createPrimarySignerIfNotExists(): void {
	// 現在使用中の署名用アドレスの情報を取得する処理で自動作成される
	// self::getPrimarySignerInfo();
	// }

	/**
	 * @return string 現在サイトで使用している署名用アドレス
	 */
	public static function getPrimarySignerAddress(): string {
		return Signer::fromPrivateKey( self::getPrimarySignerPrivateKey() )->getAddress();
	}

	/**
	 * @return string 現在サイトで使用している署名用秘密鍵
	 */
	public static function getPrimarySignerPrivateKey(): string {
		return self::getPrimarySignerInfo()['private_key'];
	}

	/**
	 * @return bool 引数で指定した署名用アドレスがこのサイトで使用されているかどうか(DBに存在するかどうか)
	 */
	public static function isSignerExists( string $address ): bool {
		$addresses = self::getSignerAddresses();
		foreach ( $addresses as $addr ) {
			if ( strtolower( $addr ) === strtolower( $address ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 署名用アドレス一覧を取得します。
	 *
	 * 現在使用している署名用アドレス及び過去に使用していた署名用アドレス全てを取得します。
	 *
	 * @return string[]
	 */
	public static function getSignerAddresses(): array {
		$result   = array();
		$result[] = self::getPrimarySignerAddress();

		// TODO: 過去に使用したことのある署名用アドレスも取得する
		// TODO: DBの情報はほぼ変更がないのでキャッシュする

		return $result;
	}

	/**
	 * チェーンIDに対するRPC URLを取得します
	 *
	 * @param int $chain_id
	 * @return null|string
	 * @deprecated TODO: Settings.php へ移動
	 */
	public static function getRpcUrl( int $chain_id ): ?string {
		$rpc_urls = get_option( OptionKey::rpcUrls(), array() );
		return $rpc_urls[ $chain_id ] ?? null;
	}

	/**
	 * RPC URLの一覧を取得します。
	 *
	 * @return array<int,string>
	 * format: { 1: "https://xxxxx", 137: "https://xxxxx" }
	 */
	public static function getRpcUrls(): array {
		return get_option( OptionKey::rpcUrls(), array() );
	}

	public static function setRpcUrl( int $chain_id, ?string $rpc_url ): void {
		/*
		format:
			{
				1: "https://xxxxx",
				137: "https://xxxxx"
			}
		 */
		$key      = OptionKey::rpcUrls();
		$rpc_urls = get_option( $key, array() );
		if ( is_null( $rpc_url ) || strlen( $rpc_url ) === 0 ) {
			unset( $rpc_urls[ $chain_id ] );
		} else {
			$rpc_urls[ $chain_id ] = $rpc_url;
		}
		update_option( $key, $rpc_urls, true );
	}

	public static function getInitialBlockNumber( int $chain_id ): ?string {
		$initial_block_numbers = get_option( Constants::get( 'optionsKey.initialBlockNumbers' ), array() );
		return $initial_block_numbers[ $chain_id ] ?? null;
	}
	public static function setInitialBlockNumber( int $chain_id, string $block_number ): void {
		/*
		format:
			{
				"1": "0x12345678",
				"137": "0x12345678"
			}
		 */
		$key                   = Constants::get( 'optionsKey.initialBlockNumbers' );
		$initial_block_numbers = get_option( $key, array() );

		// すでにブロック番号が設定されている場合はエラー
		if ( isset( $initial_block_numbers[ $chain_id ] ) ) {
			Logger::error( "{$chain_id} is already exists. - " . $initial_block_numbers[ $chain_id ] );
			throw new \Exception( '{5B25F425-DEFC-4DF3-8F27-1D978B647E49}' );
		}

		$initial_block_numbers[ $chain_id ] = $block_number;
		update_option( $key, $initial_block_numbers, true );
	}


	/**
	 * チェーンIDに対する待機承認数を取得します。
	 *
	 * @return array<string,int>
	 */
	public static function getTxConfirmations(): array {
		$confirmations = get_option( OptionKey::txConfirmations(), array() );
		return $confirmations;
	}

	/**
	 * 指定したチェーンIDでの承認とみなすブロック数を取得します。
	 * 1であれば、1ブロック承認された時点で承認済みとみなします。(マイニングされた時点で承認)
	 *
	 * @param int      $chain_id
	 * @param int|null $default 設定が存在しない場合に返す値
	 * @return int|null 承認とみなすブロック数
	 * @deprecated
	 */
	public static function getTxConfirmations_old( int $chain_id, ?int $default ): ?int {

		// optionから取得
		$key                 = OptionKey::txConfirmations();
		$confirmations_array = get_option( $key, array() );

		$confirmations = $confirmations_array[ $chain_id ] ?? $default;

		// $confirmationsが数値の時、値は1以上の整数であることのチェック
		if ( is_integer( $confirmations ) && $confirmations < 1 ) {
			Logger::error( "confirmations: $confirmations" );
			throw new \Exception( '{B4D140C9-49D2-45BB-A410-6A0EBF59B010}' );
		}

		return $confirmations;
	}

	/**
	 *
	 * @param int      $chain_id
	 * @param null|int $confirmations 待機するブロック数。設定を削除する場合はnullを指定する
	 * @return void
	 */
	public static function setTxConfirmations( int $chain_id, ?int $confirmations ): void {
		// 1未満の数値が指定されている場合はエラー
		if ( is_integer( $confirmations ) && $confirmations < 1 ) {
			Logger::error( "{$chain_id}'s confirmations is invalid. - {$confirmations}" );
			throw new \Exception( '{4D9653CC-7B85-4696-A0BE-AE477A3E29F8}' );
		}

		/*
		format:
			{
				"1": 1,
				"137": 1
			}
		 */
		$key                 = OptionKey::txConfirmations();
		$confirmations_array = get_option( $key, array() );

		if ( is_null( $confirmations ) ) {
			unset( $confirmations_array[ $chain_id ] );
		} else {
			$confirmations_array[ $chain_id ] = $confirmations;
		}

		update_option( $key, $confirmations_array, true );
	}


	/** 最後にPurchaseイベントのクロールを行ったブロック番号を取得します。 */
	public static function getLastCrawlPurchasedEventLogBlockNumber( int $chain_id ): string {
		$block_numbers = get_option( Constants::get( 'optionsKey.lastCrawlPurchasedEventLogBlockNumbers' ), array() );
		return $block_numbers[ $chain_id ] ?? self::getInitialBlockNumber( $chain_id );
	}


	public static function setLastCrawlPurchasedEventLogBlockNumber( int $chain_id, string $block_number_hex ): void {
		/*
		format:
			{
				"1": "0x12345678",
				"137": "0x12345678"
			}
		 */
		$key           = Constants::get( 'optionsKey.lastCrawlPurchasedEventLogBlockNumbers' );
		$block_numbers = get_option( $key, array() );

		// ブロック番号を更新
		$block_numbers[ $chain_id ] = $block_number_hex;
		update_option( $key, $block_numbers, true );
	}



	public static function getLogBlockRange( int $chain_id ): int {
		// TODO: 必要があればDBに保存

		// 2000の制限があるプロバイダが存在したため1999とする。
		// 2000ブロック
		// -> Ethereum: 約6時間40分
		// -> Polygon: 約40分
		return 1999;
	}

	/**
	 * サイト所有者が利用規約に同意したときの情報を取得します。
	 *
	 * @return array{ version: string, message: string, signature: string }|null
	 */
	public static function getSiteOwnerAgreedTermsInfo(): ?array {
		return get_option( Constants::get( 'optionsKey.siteOwnerAgreedTermsInfo' ), null );
	}

	/**
	 * サイト所有者が利用規約に同意したときの情報を登録します。
	 *
	 * @param string $version
	 * @param string $message
	 * @param string $signature
	 * @return void
	 */
	public static function setSiteOwnerAgreedTermsInfo( string $version, string $message, string $signature ): void {
		$key  = Constants::get( 'optionsKey.siteOwnerAgreedTermsInfo' );
		$info = array(
			'version'   => $version,
			'message'   => $message,
			'signature' => $signature,  // TODO: signature_hexに変更
		);
		update_option( $key, $info, true );
	}
}
