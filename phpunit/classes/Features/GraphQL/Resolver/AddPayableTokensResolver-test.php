<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Constant\ChainID;
use Cornix\Serendipity\Core\Repository\PayableTokens;
use Cornix\Serendipity\Core\Repository\TokenData;

class AddPayableTokensResolverTest extends IntegrationTestBase {

	private function requestAddPayableTokens( string $user_type, int $chain_ID, array $token_addresses ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query     = <<<GRAPHQL
			mutation AddPayableTokens(\$chainID: Int!, \$tokenAddresses: [String!]!) {
				addPayableTokens(chainID: \$chainID, tokenAddresses: \$tokenAddresses)
			}
		GRAPHQL;
		$variables = array(
			'chainID'        => $chain_ID,
			'tokenAddresses' => $token_addresses,
		);

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query, $variables )->get_data();

		return $data;
	}

	/**
	 * 管理者は支払可能なトークンを追加することができることを確認
	 *
	 * @test
	 * @testdox [A0F9A0A6][GraphQL] Add payable tokens success - user: $user_type
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestAddPayableTokensSuccess( string $user_type ) {
		$chain_ID      = ChainID::PRIVATENET_L1;
		$token_address = TestERC20Address::L1_TUSD;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認
		// トークンテーブルにERC20トークンを追加
		( new TokenData() )->addERC20( $chain_ID, $token_address );
		$GLOBALS['wpdb']->query( 'COMMIT' );

		// ACT
		$data = $this->requestAddPayableTokens( $user_type, $chain_ID, array( $token_address ) );

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
		// 登録された内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 1, count( $tokens ) );
		$this->assertEquals( $token_address, $tokens[0]->address() );
		$this->assertEquals( $chain_ID, $tokens[0]->chainID() );
	}


	/**
	 * 管理者以外は支払可能なトークンを追加することができないことを確認
	 *
	 * @test
	 * @testdox [D65CAD88][GraphQL] Add payable tokens fail - user: $user_type
	 * @dataProvider requestInvalidUsersProvider
	 */
	public function requestAddPayableTokensFail( string $user_type ) {
		$chain_ID      = ChainID::PRIVATENET_L1;
		$token_address = TestERC20Address::L1_TUSD;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認
		// トークンテーブルにERC20トークンを追加
		( new TokenData() )->addERC20( $chain_ID, $token_address );
		$GLOBALS['wpdb']->query( 'COMMIT' );

		// ACT
		$data = $this->requestAddPayableTokens( $user_type, $chain_ID, array( $token_address ) );

		// ASSERT
		$this->assertTrue( isset( $data['errors'] ) ); // エラーフィールドが存在する
		// DBの内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 0, count( $tokens ) ); // 何も登録されていない
	}

	/**
	 * 同じトークンのアドレスを追加することができないことを確認
	 *
	 * @test
	 * @testdox [D5356E66][GraphQL] Add payable tokens duplicate
	 */
	public function requestAddPayableTokensDuplicate() {
		$chain_ID      = ChainID::PRIVATENET_L1;
		$token_address = TestERC20Address::L1_TUSD;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認
		// トークンテーブルにERC20トークンを追加
		( new TokenData() )->addERC20( $chain_ID, $token_address );
		$GLOBALS['wpdb']->query( 'COMMIT' );

		// ACT
		// 同じ値を2回登録
		$this->requestAddPayableTokens( UserType::ADMINISTRATOR, $chain_ID, array( $token_address ) );
		$data = $this->requestAddPayableTokens( UserType::ADMINISTRATOR, $chain_ID, array( $token_address ) );

		// ASSERT
		$this->assertTrue( isset( $data['errors'] ) ); // エラーフィールドが存在する
		// DBの内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 1, count( $tokens ) ); // 1つだけ登録されている
	}


	/**
	 * 複数のアドレスを追加することができることを確認
	 *
	 * @test
	 * @testdox [5BCC8BB7][GraphQL] Add payable tokens multiple
	 */
	public function requestAddPayableTokensMultiple() {
		$chain_ID       = ChainID::PRIVATENET_L1;
		$token_address1 = TestERC20Address::L1_TUSD;
		$token_address2 = TestERC20Address::L1_TJPY;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認
		// トークンテーブルにERC20トークンを追加
		( new TokenData() )->addERC20( $chain_ID, $token_address1 );
		( new TokenData() )->addERC20( $chain_ID, $token_address2 );
		$GLOBALS['wpdb']->query( 'COMMIT' );

		// ACT
		$data = $this->requestAddPayableTokens( UserType::ADMINISTRATOR, $chain_ID, array( $token_address1, $token_address2 ) );

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドが存在しない
		// DBの内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 2, count( $tokens ) ); // 2つ登録されている
	}


	/**
	 * 空配列(0個のアドレス)を追加しようとしてもエラーとならないことを確認
	 *
	 * @test
	 * @testdox [3A56A576][GraphQL] Add payable tokens zero
	 */
	public function requestAddPayableTokensZero() {
		$chain_ID = ChainID::PRIVATENET_L1;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認

		// ACT
		$data = $this->requestAddPayableTokens( UserType::ADMINISTRATOR, $chain_ID, array() );  // 空配列を渡す

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドが存在しない
		// DBの内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 0, count( $tokens ) ); // 何も登録されていないまま
	}


	public function requestValidUsersProvider(): array {
		// 管理者のみ`addPayableTokens`の呼び出しが可能
		return array(
			array( UserType::ADMINISTRATOR ),
			// array( UserType::CONTRIBUTOR ),
			// array( UserType::ANOTHER_CONTRIBUTOR ),
			// array( UserType::VISITOR ),
		);
	}
	public function requestInvalidUsersProvider(): array {
		// 管理者以外は`addPayableTokens`の呼び出しに失敗
		return array(
			// array( UserType::ADMINISTRATOR ),
			array( UserType::CONTRIBUTOR ),
			array( UserType::ANOTHER_CONTRIBUTOR ),
			array( UserType::VISITOR ),
		);
	}
}
