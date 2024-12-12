<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Database\Schema\TokenTable;
use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\TokenData;

require_once 'includes/classes/Lib/Repository/RateData.php';

class TokenDataTest extends IntegrationTestBase {

	/**
	 * データをテーブルに追加するテスト
	 *
	 * @test
	 * @testdox [F6B98ADD] TokenData::add - host: $host
	 * @dataProvider addTestDataProvider
	 */
	public function addTest( string $host ): void {
		// ARRANGE
		$wpdb = WpdbFactory::create( $host );
		( new TokenTable( $wpdb ) )->drop();
		( new TokenTable( $wpdb ) )->create();

		// ACT
		$token_data = new TokenData( $wpdb );
		$token_data->add( ChainID::PRIVATENET_L1, '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707' ); // TUSD
		$wpdb->query( 'COMMIT' );

		$result = $token_data->get();

		// ASSERT
		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $result[0]->chainID() );
		$this->assertEquals( '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', $result[0]->contractAddress() );
		$this->assertEquals( 'TUSD', $result[0]->symbol() );
		$this->assertEquals( 18, $result[0]->decimals() );
	}
	public function addTestDataProvider() {
		$hosts = ( new TestDBHosts() )->get();
		return array_map(
			function ( $host ) {
				return array( $host );
			},
			$hosts
		);
	}

	/**
	 * データをテーブルから取得するテスト
	 *
	 * @test
	 * @testdox [534AF70D] TokenData::get - host: $host
	 * @dataProvider getTestDataProvider
	 */
	public function getTest( string $host ): void {
		// ARRANGE
		$wpdb = WpdbFactory::create( $host );
		( new TokenTable( $wpdb ) )->drop();
		( new TokenTable( $wpdb ) )->create();

		// ACT
		$token_data = new TokenData( $wpdb );
		$token_data->add( ChainID::PRIVATENET_L1, '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707' ); // TUSD
		$token_data->add( ChainID::PRIVATENET_L2, '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707' ); // TUSD
		$token_data->add( ChainID::PRIVATENET_L2, '0xa513E6E4b8f2a923D98304ec87F64353C4D5C853' ); // TJPY
		$wpdb->query( 'COMMIT' );

		$result_all = $token_data->get();
		$result_eth = $token_data->get( ChainID::ETH_MAINNET );     // イーサリアムメインネットのトークン情報(追加していないため0件)
		$result_l1  = $token_data->get( ChainID::PRIVATENET_L1 );    // プライベートネットL1のトークン情報(1件)
		$result_l2  = $token_data->get( ChainID::PRIVATENET_L2 );    // プライベートネットL2のトークン情報(2件)

		// ASSERT
		$this->assertEquals( 3, count( $result_all ) );
		$result_all_l1 = array_filter( $result_all, fn( $ret ) => $ret->chainID() === ChainID::PRIVATENET_L1 );
		$result_all_l2 = array_filter( $result_all, fn( $ret ) => $ret->chainID() === ChainID::PRIVATENET_L2 );
		$this->assertEquals( 1, count( $result_all_l1 ) );
		$this->assertEquals( 2, count( $result_all_l2 ) );

		$this->assertEquals( 0, count( $result_eth ) );

		$this->assertEquals( 1, count( $result_l1 ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $result_l1[0]->chainID() );
		$this->assertEquals( '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', $result_l1[0]->contractAddress() );

		$this->assertEquals( 2, count( $result_l2 ) );
		$this->assertEquals( ChainID::PRIVATENET_L2, $result_l2[0]->chainID() );
		$this->assertEquals( ChainID::PRIVATENET_L2, $result_l2[1]->chainID() );
		$this->assertContains( '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', array_map( fn( $ret ) => $ret->contractAddress(), $result_l2 ) );
		$this->assertContains( '0xa513E6E4b8f2a923D98304ec87F64353C4D5C853', array_map( fn( $ret ) => $ret->contractAddress(), $result_l2 ) );
	}
	public function getTestDataProvider() {
		$hosts = ( new TestDBHosts() )->get();
		return array_map(
			function ( $host ) {
				return array( $host );
			},
			$hosts
		);
	}
}
