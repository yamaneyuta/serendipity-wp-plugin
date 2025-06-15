<?php
require_once __DIR__ . '/../includes/vendor/autoload.php';

/*
 * 支払情報をリセットし、同一ページで再度購入処理を実行できる状態にします。
 *
 *
 * このスクリプトをpackage.jsonから呼び出されます。
 */

use Cornix\Serendipity\Core\Repository\Environment;
use Cornix\Serendipity\Core\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\Infrastructure\Database\Service\DatabaseMigrationService;
use Cornix\Serendipity\Core\Application\Factory\ServerSignerServiceFactory;

// ■ 方針:
// 署名用ウォレットを再作成し、署名用ウォレットに紐づく情報をすべて削除する
//
// ■ 処理フロー:
// 1. 一旦、不要なデータをすべて削除する
// 2. テーブルをリネームし、疑似的にテーブルが削除された状態にする
// 3. マイグレーションを実行して、テーブルを再生成する
// 4. 署名用ウォレットデータの内容をリネームしたテーブルへ移動
// 5. マイグレーションで作成されたテーブルをすべて削除し、バックアップのテーブルを元に戻す

global $wpdb;

$server_signer_service = ( new ServerSignerServiceFactory() )->create( $wpdb );

// 変更前の署名用ウォレットアドレスを取得(最後に表示するために保持)
$prevAddress = $server_signer_service->getServerSigner()->address();

// 1. 一旦、不要なデータをすべて削除する
$delete_table_names = array(
	( new TableName() )->unlockPaywallTransaction(),
	( new TableName() )->unlockPaywallTransferEvent(),
	( new TableName() )->invoice(),
	( new TableName() )->serverSigner(),
);
foreach ( $delete_table_names as $table_name ) {
	$wpdb->query( "TRUNCATE TABLE `{$table_name}`" );
}

// 2. テーブルをリネームし、疑似的にテーブルが削除された状態にする
$table_prefix = ( new Prefix() )->tableNamePrefix();
// $table_prefix で開始するテーブル名をすべて取得
$table_names = $wpdb->get_col( "SHOW TABLES LIKE '{$table_prefix}%'" );
foreach ( $table_names as $table_name ) {
	// テーブル名をリネーム
	$wpdb->query( "RENAME TABLE `{$table_name}` TO `bak_{$table_name}`" );
}

// 3. マイグレーションを実行して、テーブルを再生成する
( new DatabaseMigrationService( $wpdb, new Environment() ) )->migrate( null );

// 4. 署名用ウォレットデータの内容をリネームしたテーブルへ移動
$server_signer_table_name = ( new TableName() )->serverSigner();
$result                   = $wpdb->query( "INSERT INTO `bak_{$server_signer_table_name}` SELECT * FROM `{$server_signer_table_name}`" );
if ( false === $result ) {
	throw new \RuntimeException( '[D91480C9] Failed to copy server signer data: ' . $wpdb->last_error );
}

// 5. マイグレーションで作成されたテーブルをすべて削除し、バックアップのテーブルを元に戻す
$table_names = $wpdb->get_col( "SHOW TABLES LIKE '{$table_prefix}%'" );
foreach ( $table_names as $table_name ) {
	$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`;" );
	$wpdb->query( "RENAME TABLE `bak_{$table_name}` TO `{$table_name}`;" );
}

// 新しく生成された署名用ウォレットアドレスを取得
$newAddress = $server_signer_service->getServerSigner()->address();
echo "Server Signer Data has been reset.\n";
echo "Old Address: $prevAddress\n";
echo "New Address: $newAddress\n";
