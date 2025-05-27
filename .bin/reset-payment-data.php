<?php
require_once __DIR__ . '/../includes/vendor/autoload.php';

/*
 * 支払情報をリセットし、同一ページで再度購入処理を実行できる状態にします。
 *
 *
 * このスクリプトをpackage.jsonから呼び出されます。
 */

use Cornix\Serendipity\Core\Lib\Database\Schema\InvoiceNonceTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\InvoiceTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\UnlockPaywallTransactionTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\Repository\ServerSignerData;

// 方針:
// テストネットで確認することもあるため、コントラクトのデータは変更せず以下の処理を実施
// - 署名用ウォレットの秘密鍵を新規作成
// - 請求書や履歴のデータを削除

$server_signer_data = new ServerSignerData();
$prevAddress        = $server_signer_data->getAddress();
$server_signer_data->initialize( $force = true );
$newAddress = $server_signer_data->getAddress();

echo "Server Signer Data has been reset.\n";
echo "Old Address: $prevAddress\n";
echo "New Address: $newAddress\n";

// テーブルを再作成することで履歴を削除
global $wpdb;
( new UnlockPaywallTransactionTable( $wpdb ) )->drop();
( new UnlockPaywallTransactionTable( $wpdb ) )->create();
( new UnlockPaywallTransferEventTable( $wpdb ) )->drop();
( new UnlockPaywallTransferEventTable( $wpdb ) )->create();
( new InvoiceTable( $wpdb ) )->drop();
( new InvoiceTable( $wpdb ) )->create();
( new InvoiceNonceTable( $wpdb ) )->drop();
( new InvoiceNonceTable( $wpdb ) )->create();
