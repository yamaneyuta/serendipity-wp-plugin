<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Repository\TableGateway\TokenTable;
use Cornix\Serendipity\Core\Constant\ChainID;
use Cornix\Serendipity\Core\Entity\Token;
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
		$token_table->save( Token::from( ChainID::PRIVATENET_L1, TestERC20Address::L1_TUSD(), 'TUSD', 18, true ) );

		$result = $token_table->all();

		// ASSERT
		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $result[0]->chain_id );
		$this->assertEquals( TestERC20Address::L1_TUSD()->value(), $result[0]->address );
		$this->assertEquals( 'TUSD', $result[0]->symbol );
		$this->assertEquals( 18, $result[0]->decimals );
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
		$token_table->save( Token::from( ChainID::PRIVATENET_L1, Ethers::zeroAddress(), 'ETH', 18, true ) );

		// ASSERT
		$result = $token_table->all();
		$this->assertEquals( 1, count( $result ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $result[0]->chain_id );
		$this->assertEquals( Ethers::zeroAddress(), $result[0]->address );
		$this->assertEquals( 'ETH', $result[0]->symbol );
		$this->assertEquals( 18, $result[0]->decimals );
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
		$token_table->save( Token::from( ChainID::PRIVATENET_L1, TestERC20Address::L1_TUSD(), 'TUSD', 18, true ) );
		$token_table->save( Token::from( ChainID::PRIVATENET_L2, TestERC20Address::L2_TUSD(), 'TUSD', 18, true ) );
		$token_table->save( Token::from( ChainID::PRIVATENET_L2, TestERC20Address::L2_TJPY(), 'TJPY', 18, true ) );

		$result_all = $token_table->all();
		$result_eth = $token_table->all( ChainID::ETH_MAINNET );     // イーサリアムメインネットのトークン情報(追加していないため0件)
		$result_l1  = $token_table->all( ChainID::PRIVATENET_L1 );    // プライベートネットL1のトークン情報(1件)
		$result_l2  = $token_table->all( ChainID::PRIVATENET_L2 );    // プライベートネットL2のトークン情報(2件)

		// ASSERT
		$this->assertEquals( 3, count( $result_all ) );
		$result_all_l1 = array_filter( $result_all, fn( $ret ) => $ret->chain_id === ChainID::PRIVATENET_L1 );
		$result_all_l2 = array_filter( $result_all, fn( $ret ) => $ret->chain_id === ChainID::PRIVATENET_L2 );
		$this->assertEquals( 1, count( $result_all_l1 ) );
		$this->assertEquals( 2, count( $result_all_l2 ) );

		$this->assertEquals( 0, count( $result_eth ) );

		$this->assertEquals( 1, count( $result_l1 ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $result_l1[0]->chain_id );
		$this->assertEquals( TestERC20Address::L1_TUSD()->value(), $result_l1[0]->address );

		$this->assertEquals( 2, count( $result_l2 ) );
		$this->assertEquals( ChainID::PRIVATENET_L2, $result_l2[0]->chain_id );
		$this->assertEquals( ChainID::PRIVATENET_L2, $result_l2[1]->chain_id );
		$this->assertContains( TestERC20Address::L2_TUSD()->value(), array_map( fn( $ret ) => $ret->address, $result_l2 ) );
		$this->assertContains( TestERC20Address::L2_TJPY()->value(), array_map( fn( $ret ) => $ret->address, $result_l2 ) );
	}
}
