<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Definition\TokenDefinition;
use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;
use Cornix\Serendipity\Core\Types\Token;

class RemovePayableTokensResolverTest extends IntegrationTestBase {

	private function requestRemovePayableTokens( string $user_type, int $chain_ID, array $token_addresses ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query     = <<<GRAPHQL
			mutation RemovePayableTokens(\$chainID: Int!, \$tokenAddresses: [String!]!) {
				removePayableTokens(chainID: \$chainID, tokenAddresses: \$tokenAddresses)
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
	 * 管理者は支払可能なトークンを削除することができることを確認
	 *
	 * @test
	 * @testdox [9B7B9D5D][GraphQL] Remove payable tokens success - user: $user_type
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestRemovePayableTokensSuccess( string $user_type ) {
		$chain_ID = ChainID::PRIVATENET_L1;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認
		// 1つ登録
		$register_token_address = ( new TokenDefinition() )->all( $chain_ID )[0]->address();
		( new PayableTokens() )->save( $chain_ID, array( Token::from( $chain_ID, $register_token_address ) ) );
		assert( 1 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 1つ登録済みになったことを確認

		// ACT
		$data = $this->requestRemovePayableTokens( $user_type, $chain_ID, array( $register_token_address ) );

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
		// 登録された内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 0, count( $tokens ) );
	}


	/**
	 * 管理者以外は支払可能なトークンを削除することができないことを確認
	 *
	 * @test
	 * @testdox [7BD7D70B][GraphQL] Remove payable tokens fail - user: $user_type
	 * @dataProvider requestInvalidUsersProvider
	 */
	public function requestRemovePayableTokensFail( string $user_type ) {
		$chain_ID = ChainID::PRIVATENET_L1;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認
		// 1つ登録
		$register_token_address = ( new TokenDefinition() )->all( $chain_ID )[0]->address();
		( new PayableTokens() )->save( $chain_ID, array( Token::from( $chain_ID, $register_token_address ) ) );
		assert( 1 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 1つ登録済みになったことを確認

		// ACT
		$data = $this->requestRemovePayableTokens( $user_type, $chain_ID, array( $register_token_address ) );

		// ASSERT
		$this->assertTrue( isset( $data['errors'] ) ); // エラーフィールドが存在する
		// DBの内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 1, count( $tokens ) ); // 何も削除されていない
		$this->assertEquals( $chain_ID, $tokens[0]->chainID() );
		$this->assertEquals( $register_token_address, $tokens[0]->address() );
	}

	/**
	 * 保存されていないトークンアドレスを削除することができないことを確認
	 *
	 * @test
	 * @testdox [0CDD9B40][GraphQL] Remove payable tokens duplicate
	 */
	public function requestRemovePayableTokensDuplicate() {
		$chain_ID = ChainID::PRIVATENET_L1;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認
		// 1つ登録
		$register_token_address = ( new TokenDefinition() )->all( $chain_ID )[0]->address();
		( new PayableTokens() )->save( $chain_ID, array( Token::from( $chain_ID, $register_token_address ) ) );
		assert( 1 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 1つ登録済みになったことを確認
		$not_registered_token_address = ( new TokenDefinition() )->all( $chain_ID )[1]->address();    // 登録されていないアドレス

		// ACT
		$data = $this->requestRemovePayableTokens( UserType::ADMINISTRATOR, $chain_ID, array( $not_registered_token_address ) );

		// ASSERT
		$this->assertTrue( isset( $data['errors'] ) ); // エラーフィールドが存在する
		// DBの内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 1, count( $tokens ) ); // 登録されているトークンは1つのまま
		$this->assertEquals( $chain_ID, $tokens[0]->chainID() );
		$this->assertEquals( $register_token_address, $tokens[0]->address() );
	}


	/**
	 * 複数のアドレスを削除することができることを確認
	 *
	 * @test
	 * @testdox [00B8C90C][GraphQL] Remove payable tokens multiple
	 */
	public function requestRemovePayableTokensMultiple() {
		$chain_ID = ChainID::PRIVATENET_L1;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認
		// 登録するトークンアドレスを取得
		$register_token_addresses = array_map( fn( $token ) => $token->address(), ( new TokenDefinition() )->all( $chain_ID ) );
		// 3つ登録
		$token1 = Token::from( $chain_ID, $register_token_addresses[0] );
		$token2 = Token::from( $chain_ID, $register_token_addresses[1] );
		$token3 = Token::from( $chain_ID, $register_token_addresses[2] );
		( new PayableTokens() )->save( $chain_ID, array( $token1, $token2, $token3 ) );

		// ACT
		$data = $this->requestRemovePayableTokens( UserType::ADMINISTRATOR, $chain_ID, array( $register_token_addresses[1], $register_token_addresses[2] ) );

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドが存在しない
		// DBの内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 1, count( $tokens ) ); // 3つ登録後2つ削除したので1つだけ残っている
		$this->assertEquals( $chain_ID, $tokens[0]->chainID() );
		$this->assertEquals( $register_token_addresses[0], $tokens[0]->address() );
	}


	/**
	 * 空配列(0個のアドレス)を削除しようとしてもエラーとならないことを確認
	 *
	 * @test
	 * @testdox [4100FF09][GraphQL] Remove payable tokens zero
	 */
	public function requestRemovePayableTokensZero() {
		$chain_ID = ChainID::PRIVATENET_L1;
		// ARRANGE
		// 一旦保存されているトークン一覧を削除
		( new PayableTokens() )->save( $chain_ID, array() );
		assert( 0 === count( ( new PayableTokens() )->get( $chain_ID ) ) ); // 空になったことを確認

		// ACT
		$data = $this->requestRemovePayableTokens( UserType::ADMINISTRATOR, $chain_ID, array() );  // 空配列を渡す

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドが存在しない
		// DBの内容を確認
		$tokens = ( new PayableTokens() )->get( $chain_ID );
		$this->assertEquals( 0, count( $tokens ) ); // 何も登録されていないまま
	}


	public function requestValidUsersProvider(): array {
		// 管理者のみ`removePayableTokens`の呼び出しが可能
		return array(
			array( UserType::ADMINISTRATOR ),
			// array( UserType::CONTRIBUTOR ),
			// array( UserType::ANOTHER_CONTRIBUTOR ),
			// array( UserType::VISITOR ),
		);
	}
	public function requestInvalidUsersProvider(): array {
		// 管理者以外は`removePayableTokens`の呼び出しに失敗
		return array(
			// array( UserType::ADMINISTRATOR ),
			array( UserType::CONTRIBUTOR ),
			array( UserType::ANOTHER_CONTRIBUTOR ),
			array( UserType::VISITOR ),
		);
	}
}