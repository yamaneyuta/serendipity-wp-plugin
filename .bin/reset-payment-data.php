<?php
require_once __DIR__ . '/../includes/vendor/autoload.php';

/*
 * 支払情報をリセットし、同一ページで再度購入処理を実行できる状態にします。
 *
 *
 * このスクリプトをpackage.jsonから呼び出されます。
 */

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ServerSignerTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\UnlockPaywallTransactionTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\Service\Factory\ServerSignerServiceFactory;

// 方針:
// テストネットで確認することもあるため、コントラクトのデータは変更せず以下の処理を実施
// - 署名用ウォレットの秘密鍵を新規作成
// - 請求書や履歴のデータを削除

global $wpdb;

$server_signer_service = ( new ServerSignerServiceFactory() )->create( $wpdb );
$prevAddress           = $server_signer_service->getServerSigner()->address();
( new ServerSignerTable( $wpdb ) )->drop();
( new ServerSignerTable( $wpdb ) )->create();
$server_signer_service->initializeServerSigner();
$newAddress = $server_signer_service->getServerSigner()->address();

echo "Server Signer Data has been reset.\n";
echo "Old Address: $prevAddress\n";
echo "New Address: $newAddress\n";

// テーブルを再作成することで履歴を削除
( new UnlockPaywallTransactionTable( $wpdb ) )->drop();
( new UnlockPaywallTransactionTable( $wpdb ) )->create();
( new UnlockPaywallTransferEventTable( $wpdb ) )->drop();
( new UnlockPaywallTransferEventTable( $wpdb ) )->create();
( new InvoiceTable( $wpdb ) )->drop();
( new InvoiceTable( $wpdb ) )->create();
