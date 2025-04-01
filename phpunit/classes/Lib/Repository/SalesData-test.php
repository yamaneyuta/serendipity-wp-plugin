<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Database\Schema\InvoiceTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\TokenTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\UnlockPaywallTransactionTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Repository\SalesData;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\SalesDataType;

class SalesDataTest extends IntegrationTestBase {

	/**
	 * 各データベースのバージョンでSalesData::selectで売上データを取得できることを確認
	 * 取得したデータの各項目の形式が正しいことを確認
	 *
	 * @test
	 * @testdox [87F7BA82] SalesData::select - host: $host
	 * @dataProvider selectDataProvider
	 */
	public function select( string $host ) {
		// ARRANGE
		$wpdb = WpdbFactory::create( $host );
		( new SalesDataTablesInitializer( $wpdb ) )->initialize();    // テーブルを初期化
		$this->insertTableData( $wpdb );
		$sut = new SalesData( $wpdb );

		// ACT
		$results = $sut->select();

		// ASSERT
		$this->assertIsArray( $results );
		$this->assertGreaterThanOrEqual( 1, count( $results ) );
		$sales_data = $results[0];
		$this->assertEquals( 26, strlen( $sales_data->invoiceID() ) );  // invoiceIDはULIDなので26文字
		$this->assertGreaterThan( 0, $sales_data->postID() );
		$this->assertGreaterThan( 0, $sales_data->chainID() );
		$selling_price = $sales_data->sellingPrice();
		$this->assertTrue( Judge::isHex( $selling_price->amountHex() ) );
		$this->assertGreaterThanOrEqual( 0, $selling_price->decimals() );
		$this->assertTrue( Judge::isSymbol( $selling_price->symbol() ) );
		$this->assertTrue( Judge::isAddress( $sales_data->sellerAddress() ) );
		$payment_price = $sales_data->paymentPrice();
		$this->assertTrue( Judge::isHex( $payment_price->amountHex() ) );
		$this->assertGreaterThanOrEqual( 0, $payment_price->decimals() );
		$this->assertTrue( Judge::isSymbol( $payment_price->symbol() ) );
		$this->assertTrue( Judge::isAddress( $sales_data->consumerAddress() ) );
		$this->assertGreaterThan( 0, $sales_data->createdAt()->getTimestamp() );
		$this->assertGreaterThan( 0, $sales_data->blockNumber() );
		$this->assertEquals( 66, strlen( $sales_data->transactionHash() ) );    // transactionHashは64文字
		$seller_profit_price = $sales_data->sellerProfitPrice();
		$this->assertTrue( Judge::isHex( $seller_profit_price->amountHex() ) );
		$this->assertGreaterThanOrEqual( 0, $seller_profit_price->decimals() );
		$this->assertTrue( Judge::isSymbol( $seller_profit_price->symbol() ) );
		$handling_fee_price = $sales_data->handlingFeePrice();
		$this->assertTrue( Judge::isHex( $handling_fee_price->amountHex() ) );
		$this->assertGreaterThanOrEqual( 0, $handling_fee_price->decimals() );
		$this->assertTrue( Judge::isSymbol( $handling_fee_price->symbol() ) );
		$payment_token = $sales_data->paymentToken();
		$this->assertTrue( Judge::isAddress( $payment_token->address() ) );
		$this->assertTrue( Judge::isSymbol( $payment_token->symbol() ) );
		$this->assertGreaterThanOrEqual( 0, $payment_token->decimals() );
		$this->assertGreaterThan( 0, $payment_token->chainID() );

		$this->assertEquals( $payment_price->symbol(), $seller_profit_price->symbol() );    // 支払い時の通貨は利益の通貨と一致
		$this->assertEquals( $payment_price->decimals(), $seller_profit_price->decimals() );// 支払い時の通貨は利益の通貨と一致
		$this->assertEquals( $payment_price->symbol(), $handling_fee_price->symbol() );     // 支払い時の通貨は手数料の通貨と一致
		$this->assertEquals( $payment_price->decimals(), $handling_fee_price->decimals() ); // 支払い時の通貨は手数料の通貨と一致
		$this->assertEquals( $payment_price->symbol(), $payment_token->symbol() );          // 支払い時の通貨は支払いトークンの通貨と一致
		$this->assertEquals( $payment_price->decimals(), $payment_token->decimals() );      // 支払い時の通貨は支払いトークンの通貨と一致
	}

	/**
	 * 各データベースのバージョンでSalesData::selectで売上データを複数件取得できることを確認
	 *
	 * @test
	 * @testdox [0AAF9A7C] SalesData::select (2 rows) - host: $host
	 * @dataProvider selectDataProvider
	 */
	public function selectMultiRecords( string $host ) {
		// ARRANGE
		$wpdb = WpdbFactory::create( $host );
		( new SalesDataTablesInitializer( $wpdb ) )->initialize();    // テーブルを初期化
		$this->insertTableData( $wpdb );    // 1件目のデータを挿入
		$this->insertTableData2( $wpdb );   // 2件目のデータを挿入
		$sut = new SalesData( $wpdb );

		// ACT
		$results = $sut->select();  // 全件取得

		// ASSERT
		$this->assertIsArray( $results );
		$this->assertGreaterThanOrEqual( 2, count( $results ) );
		$invoice_ids = array_map( fn( SalesDataType $sales_data ) => $sales_data->invoiceID(), $results );
		$this->assertContains( '01JKFB56B8PQQ261K5VZDCE5DH', $invoice_ids );    // 1件目のデータのinvoice_idが含まれていること
		$this->assertContains( '01JKFZQE7YDYVHG97BBXWY4WTJ', $invoice_ids );    // 2件目のデータのinvoice_idが含まれていること
	}

	public function selectDataProvider() {
		return ( new TestPattern() )->createDBHostMatrix();   // 各DBでテスト
	}

	/**
	 * 以下の内容で履歴を記録します。
	 * 　　販売者: 0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266
	 * 　　投稿ID: 49
	 * 　　販売価格: 1,000JPY
	 * 　　購入者: 0x70997970C51812dc3A010C7d01b50e0d17dc79C8
	 * 　　購入トークン: ETH
	 * 　　請求書ID: 01JKFB56B8PQQ261K5VZDCE5DH
	 */
	private function insertTableData( wpdb $wpdb ): void {
		$sales_test_data = new SalesTestData( $wpdb );

		// invoiceテーブルへデータ挿入
		$sales_test_data->insertInvoiceData( '2025-02-07 04:37:14', '01JKFB56B8PQQ261K5VZDCE5DH', '49', '31337', '0x3e8', '0', 'JPY', '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', '0x0000000000000000000000000000000000000000', '0x089e9b46556754', '0x70997970C51812dc3A010C7d01b50e0d17dc79C8' );

		// unlock_paywall_transactionテーブルへデータ挿入
		$sales_test_data->insertTransactionData( '2025-02-07 04:58:04', '01JKFB56B8PQQ261K5VZDCE5DH', '31337', '276', '0x7117fd9b43492484bf18d93a834de4c39ec2e00687ee235594b77129426bb236' );

		// unlock_paywall_transfer_eventテーブルへデータ挿入
		$sales_test_data->insertTransferEventData( '2025-02-07 04:58:04', '01JKFB56B8PQQ261K5VZDCE5DH', '0', '0x70997970C51812dc3A010C7d01b50e0d17dc79C8', '0x8A791620dd6260079BF849Dc5567aDC3F2FdC318', '0x0000000000000000000000000000000000000000', '0x1610e9a9d064' );
		$sales_test_data->insertTransferEventData( '2025-02-07 04:58:05', '01JKFB56B8PQQ261K5VZDCE5DH', '1', '0x70997970C51812dc3A010C7d01b50e0d17dc79C8', '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', '0x0000000000000000000000000000000000000000', '0x08888a5cab96f0' );
	}

	/**
	 * 以下の内容で履歴を記録します。
	 * 　　販売者: 0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266
	 * 　　投稿ID: 49
	 * 　　販売価格: 1,000JPY
	 * 　　購入者: 0x3C44CdDdB6a900fa2b585dd299e03d12FA4293BC
	 * 　　購入トークン: ETH
	 * 　　請求書ID: 01JKFZQE7YDYVHG97BBXWY4WTJ
	 */
	private function insertTableData2( wpdb $wpdb ): void {
		$sales_test_data = new SalesTestData( $wpdb );

		// invoiceテーブルへデータ挿入
		$sales_test_data->insertInvoiceData( '2025-02-07 10:36:48', '01JKFZQE7YDYVHG97BBXWY4WTJ', '49', '31337', '0x3e8', '0', 'JPY', '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', '0x0000000000000000000000000000000000000000', '0x087f79088eac8e', '0x3C44CdDdB6a900fa2b585dd299e03d12FA4293BC' );

		// unlock_paywall_transactionテーブルへデータ挿入
		$sales_test_data->insertTransactionData( '2025-02-07 10:50:25', '01JKFZQE7YDYVHG97BBXWY4WTJ', '31337', '2068', '0xe6355e851a760d4bb2c283f59fc0cc6af03d983341671ba9789b475ab6d2c4ce' );

		// unlock_paywall_transfer_eventテーブルへデータ挿入
		$sales_test_data->insertTransferEventData( '2025-02-07 10:50:25', '01JKFZQE7YDYVHG97BBXWY4WTJ', '0', '0x3C44CdDdB6a900fa2b585dd299e03d12FA4293BC', '0x8A791620dd6260079BF849Dc5567aDC3F2FdC318', '0x0000000000000000000000000000000000000000', '0x15c135d8777c' );
		$sales_test_data->insertTransferEventData( '2025-02-07 10:50:25', '01JKFZQE7YDYVHG97BBXWY4WTJ', '1', '0x3C44CdDdB6a900fa2b585dd299e03d12FA4293BC', '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', '0x0000000000000000000000000000000000000000', '0x0869b7d2b63512' );
	}
}

/**
 * テストで使用するテーブルを初期化するクラス
 */
class SalesDataTablesInitializer {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function initialize() {
		$wpdb = $this->wpdb;

		// テーブルを再作成
		$tables = array(
			new TokenTable( $wpdb ),
			new InvoiceTable( $wpdb ),
			new UnlockPaywallTransactionTable( $wpdb ),
			new UnlockPaywallTransferEventTable( $wpdb ),
		);
		foreach ( $tables as $table ) {
			$table->drop();
			$table->create();
		}

		// テストで使用するトークンデータを挿入
		$token_table_name = ( new TableName() )->token();
		$ret              = $wpdb->query(
			<<<SQL
				INSERT INTO `{$token_table_name}` (chain_id, address, symbol, decimals)
				VALUES
					(31337, '0x0000000000000000000000000000000000000000', 'ETH', 18),
					( 1337, '0x0000000000000000000000000000000000000000', 'ETH', 18);
			SQL
		);
		assert( 2 === $ret, '[86B116D3]' . $wpdb->last_error );
	}
}

/**
 * 購入時に記録されるデータを直接DBに挿入するクラス
 */
class SalesTestData {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	public function insertInvoiceData( $created_at, $id, $post_id, $chain_id, $selling_amount_hex, $selling_decimals, $selling_symbol, $seller_address, $payment_token_address, $payment_amount_hex, $consumer_address ) {
		$invoice_table_name = ( new TableName() )->invoice();
		$result             = $this->wpdb->query(
			<<<SQL
				INSERT INTO `{$invoice_table_name}` (created_at, id, post_id, chain_id, selling_amount_hex, selling_decimals, selling_symbol, seller_address, payment_token_address, payment_amount_hex, consumer_address)
				VALUES ('$created_at', '$id', $post_id, $chain_id, '$selling_amount_hex', $selling_decimals, '$selling_symbol', '$seller_address', '$payment_token_address', '$payment_amount_hex', '$consumer_address');
			SQL
		);
		assert( 1 === $result, '[E54B5988] ' . $this->wpdb->last_error );
	}

	public function insertTransactionData( $created_at, $invoice_id, $chain_id, $block_number, $transaction_hash ) {
		$transaction_table_name = ( new TableName() )->unlockPaywallTransaction();
		$result                 = $this->wpdb->query(
			<<<SQL
				INSERT INTO `{$transaction_table_name}` (created_at, invoice_id, chain_id, block_number, transaction_hash)
				VALUES ('$created_at', '$invoice_id', $chain_id, $block_number, '$transaction_hash');
			SQL
		);
		assert( 1 === $result, '[9811DF9A] ' . $this->wpdb->last_error );
	}

	public function insertTransferEventData( $created_at, $invoice_id, $log_index, $from_address, $to_address, $token_address, $amount_hex ) {
		$transfer_event_table_name = ( new TableName() )->unlockPaywallTransferEvent();
		$result                    = $this->wpdb->query(
			<<<SQL
				INSERT INTO `{$transfer_event_table_name}` (created_at, invoice_id, log_index, from_address, to_address, token_address, amount_hex)
				VALUES ('$created_at', '$invoice_id', $log_index, '$from_address', '$to_address', '$token_address', '$amount_hex');
			SQL
		);
		assert( 1 === $result, '[1C46B20E] ' . $this->wpdb->last_error );
	}
}
