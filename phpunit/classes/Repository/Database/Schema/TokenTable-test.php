<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Database\Schema\TokenTable;
use Cornix\Serendipity\Core\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;

require_once 'includes/classes/Repository/RateData.php';

class TokenTableTest extends IntegrationTestBase {

	/**
	 * データを追加するテスト
	 *
	 * @test
	 * @testdox [F6B98ADD] TokenData::add - host: $host
	 * @dataProvider hostDataProvider
	 */
	public function addTest( string $host ): void {
		// ARRANGE
		$wpdb        = WpdbFactory::create( $host );
		$token_table = new TokenTable( $wpdb );
		$token_table->drop();
		$token_table->create();

		// ACT
		$token_table->insert( ChainID::PRIVATENET_L1, '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', 'TUSD', 18 );

		$result = $token_table->select();

		// ASSERT
		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $result[0]->chainID() );
		$this->assertEquals( '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', $result[0]->address() );
		$this->assertEquals( 'TUSD', $result[0]->symbol() );
		$this->assertEquals( 18, $result[0]->decimals() );
	}
	public function hostDataProvider() {
		$hosts = ( new TestDBHosts() )->get();
		return array_map(
			fn( string $host ) => array( $host ),
			$hosts
		);
	}

	/**
	 * アドレスゼロのトークンでも追加できることを確認するテスト
	 *
	 * @test
	 * @testdox [DCE5177B] TokenData::add - zero address - host: $host
	 * @dataProvider hostDataProvider
	 */
	public function addZeroAddressTest( string $host ) {
		// ARRANGE
		$wpdb        = WpdbFactory::create( $host );
		$token_table = new TokenTable( $wpdb );
		$token_table->drop();
		$token_table->create();

		// ACT
		$token_table->insert( ChainID::PRIVATENET_L1, Ethers::zeroAddress(), 'ETH', 18 );

		// ASSERT
		$result = $token_table->select();
		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $result[0]->chainID() );
		$this->assertEquals( Ethers::zeroAddress(), $result[0]->address() );
		$this->assertEquals( 'ETH', $result[0]->symbol() );
		$this->assertEquals( 18, $result[0]->decimals() );
	}

	/**
	 * データをテーブルから取得するテスト
	 *
	 * @test
	 * @testdox [534AF70D] TokenData::get - host: $host
	 * @dataProvider hostDataProvider
	 */
	public function getTest( string $host ): void {
		// ARRANGE
		$wpdb        = WpdbFactory::create( $host );
		$token_table = new TokenTable( $wpdb );
		$token_table->drop();
		$token_table->create();

		// ACT
		$token_table->insert( ChainID::PRIVATENET_L1, '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', 'TUSD', 18 );
		$token_table->insert( ChainID::PRIVATENET_L2, '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', 'TUSD', 18 );
		$token_table->insert( ChainID::PRIVATENET_L2, '0xa513E6E4b8f2a923D98304ec87F64353C4D5C853', 'TJPY', 18 );

		$result_all = $token_table->select();
		$result_eth = $token_table->select( ChainID::ETH_MAINNET );     // イーサリアムメインネットのトークン情報(追加していないため0件)
		$result_l1  = $token_table->select( ChainID::PRIVATENET_L1 );    // プライベートネットL1のトークン情報(1件)
		$result_l2  = $token_table->select( ChainID::PRIVATENET_L2 );    // プライベートネットL2のトークン情報(2件)

		// ASSERT
		$this->assertEquals( 3, count( $result_all ) );
		$result_all_l1 = array_filter( $result_all, fn( $ret ) => $ret->chainID() === ChainID::PRIVATENET_L1 );
		$result_all_l2 = array_filter( $result_all, fn( $ret ) => $ret->chainID() === ChainID::PRIVATENET_L2 );
		$this->assertEquals( 1, count( $result_all_l1 ) );
		$this->assertEquals( 2, count( $result_all_l2 ) );

		$this->assertEquals( 0, count( $result_eth ) );

		$this->assertEquals( 1, count( $result_l1 ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $result_l1[0]->chainID() );
		$this->assertEquals( '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', $result_l1[0]->address() );

		$this->assertEquals( 2, count( $result_l2 ) );
		$this->assertEquals( ChainID::PRIVATENET_L2, $result_l2[0]->chainID() );
		$this->assertEquals( ChainID::PRIVATENET_L2, $result_l2[1]->chainID() );
		$this->assertContains( '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', array_map( fn( $ret ) => $ret->address(), $result_l2 ) );
		$this->assertContains( '0xa513E6E4b8f2a923D98304ec87F64353C4D5C853', array_map( fn( $ret ) => $ret->address(), $result_l2 ) );
	}
}
