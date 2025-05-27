<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Database\Schema\TokenTable;
use Cornix\Serendipity\Core\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Types\TokenType;

require_once 'includes/classes/Repository/RateData.php';

class TokenDataTest extends IntegrationTestBase {

	/**
	 * データを追加するテスト
	 *
	 * @test
	 * @testdox [555F5C5C] TokenData::add
	 */
	public function addTest(): void {
		// ARRANGE
		( new TokenTable() )->drop();
		( new TokenTable() )->create();
		$sut         = new TokenData();
		$prev_result = $sut->select( ChainID::PRIVATENET_L1 );   // データ追加前の状態を取得
		$add_address = TestERC20Address::L1_TUSD;

		// ACT
		$sut->addERC20( ChainID::PRIVATENET_L1, $add_address );

		$result = $sut->select( ChainID::PRIVATENET_L1 );
		$added  = array_values( array_diff( $result, $prev_result ) );

		// ASSERT
		$this->assertEquals( 1, count( $added ) );
		$this->assertEquals( ChainID::PRIVATENET_L1, $added[0]->chainID() );
		$this->assertEquals( $add_address, $added[0]->address() );
		$this->assertEquals( 'TUSD', $added[0]->symbol() );
		$this->assertEquals( 18, $added[0]->decimals() );
	}

	/**
	 * アドレスゼロのトークンは追加できないことを確認するテスト
	 *
	 * @test
	 * @testdox [0945E1FD] TokenData::add - invalid address
	 */
	public function addInvalidAddressTest() {
		// ARRANGE
		( new TokenTable() )->drop();
		( new TokenTable() )->create();

		// ACT
		$sut = new TokenData();
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( '[6006664F]' );
		$sut->addERC20( ChainID::PRIVATENET_L1, Ethers::zeroAddress() ); // ネイティブトークンは追加できないことを確認

		// ASSERT
		// Do nothing
	}

	/**
	 * トークンデータを取得するテスト
	 *
	 * @test
	 * @testdox [A64474BC] TokenData::get
	 */
	public function getTest(): void {
		// ARRANGE
		( new TokenTable() )->drop();
		( new TokenTable() )->create();

		// ACT
		$token_data = new TokenData();
		$token_data->addERC20( ChainID::PRIVATENET_L1, TestERC20Address::L1_TUSD );
		$token_data->addERC20( ChainID::PRIVATENET_L2, TestERC20Address::L2_TUSD );
		$token_data->addERC20( ChainID::PRIVATENET_L2, TestERC20Address::L2_TJPY );

		$result_eth = $token_data->select( ChainID::ETH_MAINNET );     // イーサリアムメインネットのトークン情報(追加していないため0件)
		$result_l1  = $token_data->select( ChainID::PRIVATENET_L1 );    // プライベートネットL1のトークン情報(1件)
		$result_l2  = $token_data->select( ChainID::PRIVATENET_L2 );    // プライベートネットL2のトークン情報(2件)

		// ASSERT
		// 結果からコントラクトアドレス一覧を取得するコールバック
		$get_addresses = fn( array $result ) => array_map( fn( TokenType $ret ) => $ret->address(), $result );

		$this->assertEquals( 0, count( $result_eth ) ); // 0件

		$this->assertEquals( 1, count( $result_l1 ) ); // 1件
		$this->assertEquals( ChainID::PRIVATENET_L1, $result_l1[0]->chainID() );
		$this->assertContains( TestERC20Address::L1_TUSD, $get_addresses( $result_l1 ) );

		$this->assertEquals( 2, count( $result_l2 ) ); // 2件
		$this->assertEquals( ChainID::PRIVATENET_L2, $result_l2[0]->chainID() );
		$this->assertEquals( ChainID::PRIVATENET_L2, $result_l2[1]->chainID() );
		$this->assertContains( TestERC20Address::L2_TUSD, $get_addresses( $result_l2 ) );
		$this->assertContains( TestERC20Address::L2_TJPY, $get_addresses( $result_l2 ) );
	}
}
