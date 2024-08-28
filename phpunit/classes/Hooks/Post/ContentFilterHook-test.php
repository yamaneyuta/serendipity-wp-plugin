<?php
declare(strict_types=1);


class ContentFilterHookTest extends IntegrationTestBase {

	/**
	 * トップページ(投稿一覧)に遷移した時、無料部分が表示され、ブロックと有料部分は表示されないことを確認する
	 *
	 * @test
	 * @testdox [355C84F7][Hooks] ContentFilterHook - topPage
	 */
	public function topPage() {
		// ARRANGE
		$samplePostContent = new SamplePostContent();
		// 投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => $samplePostContent->get(),
			)
		);
		// トップページへ移動
		$this->go_to( '/' );

		// ACT
		// 投稿の内容を取得
		$content = apply_filters( 'the_content', get_post( $post_ID )->post_content );

		// ASSERT
		// トップページでは無料部分だけ表示され、ブロック及び有料部分は表示されないことを確認
		$this->assertTrue( $samplePostContent->hasFreeText( $content ) );
		$this->assertFalse( $samplePostContent->hasBlock( $content ) );
		$this->assertFalse( $samplePostContent->hasPaidText( $content ) );
	}

	/**
	 * 投稿ページに遷移した時、無料部分とブロックが表示され、有料部分が表示されないことを確認する
	 *
	 * @test
	 * @testdox [10CF4252][Hooks] ContentFilterHook - permalink
	 */
	public function permalink() {
		// ARRANGE
		$samplePostContent = new SamplePostContent();
		// 投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => $samplePostContent->get(),
			)
		);
		// 投稿の個別ページへ移動
		$this->go_to( get_permalink( $post_ID ) );

		// ACT
		// 投稿の内容を取得
		$content = apply_filters( 'the_content', get_post()->post_content );

		// ASSERT
		// 個別ページでは無料部分とブロックが表示され、有料部分が表示されないことを確認
		$this->assertTrue( $samplePostContent->hasFreeText( $content ) );
		$this->assertTrue( $samplePostContent->hasBlock( $content ) );
		$this->assertFalse( $samplePostContent->hasPaidText( $content ) );
	}

	/**
	 * `/wp-json/wp/v2/posts`にアクセス(GET)したときに有料部分が表示されないことを確認する
	 *
	 * @test
	 * @testdox [62A575F7][Hooks] ContentFilterHook - /wp-json/wp/v2/posts
	 */
	public function wpV2Posts() {
		// ARRANGE
		$samplePostContent = new SamplePostContent();
		// 投稿を作成
		$contributor = $this->getUser( UserType::CONTRIBUTOR );
		$contributor->createPost(
			array(
				'post_content' => $samplePostContent->get(),
			)
		);

		// ACT
		// `/wp-json/wp/v2/posts`へアクセス(WP_REST_Requestの第二引数で`/wp-json`は記述しない)
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = rest_do_request( $request );

		// ASSERT
		$this->assertEquals( 200, $response->get_status() ); // RESTのレスポンスが正常であること
		// bodyを取得
		$body    = $response->get_data();
		$content = wp_json_encode( $body );   // json形式の文字列を判定対象とする
		// 無料部分のみ取得でき、ブロック及び有料部分は非表示であることを確認
		$this->assertTrue( $samplePostContent->hasFreeText( $content ) );
		$this->assertFalse( $samplePostContent->hasBlock( $content ) );
		$this->assertFalse( $samplePostContent->hasPaidText( $content ) );
	}


	/**
	 * ブロックが含まれない投稿を作成した場合、登録した内容で(フィルタされずに)表示されることを確認する
	 *
	 * @test
	 * @testdox [2C260893][Hooks] ContentFilterHook - no block
	 */
	public function noBlock() {
		// ARRANGE
		// 投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => '<p>This is no block content.</p>',
			)
		);
		// トップページへ移動
		$this->go_to( '/' );

		// ACT
		// 投稿の内容を取得
		$content = apply_filters( 'the_content', get_post( $post_ID )->post_content );

		// ASSERT
		// ブロックが含まれない投稿の場合、登録した内容で(フィルタされずに)表示されることを確認
		$this->assertEquals( '<p>This is no block content.</p>', trim( $content ) );  // 改行が含まれるためtrim
	}

	/**
	 * get_the_content_feed()を用いてフィードの内容を取得した場合、無料部分のみが表示されることを確認する
	 *
	 * @test
	 * @testdox [727D2C6F][Hooks] ContentFilterHook - get_the_content_feed feed_type: $feed_type
	 * @dataProvider getTheContentFeedDataProvider
	 */
	public function getTheContentFeed( string $feed_type ) {
		// ARRANGE
		$samplePostContent = new SamplePostContent();
		// 投稿を作成
		$contributor = $this->getUser( UserType::CONTRIBUTOR );
		$post_ID     = $contributor->createPost(
			array(
				'post_content' => $samplePostContent->get(),
			)
		);

		// ACT
		// フィードのURLへ移動
		$this->go_to( '/?feed=' . $feed_type );
		// グローバルオブジェクトに設定していないとテストに失敗するため`go_to`の後に`setup_postdata`を実行
		setup_postdata( get_post( $post_ID ) );
		// フィードを取得
		$feed = get_the_content_feed( $feed_type );

		// ASSERT
		$this->assertTrue( $samplePostContent->hasFreeText( $feed ) );
		$this->assertFalse( $samplePostContent->hasBlock( $feed ) );
		$this->assertFalse( $samplePostContent->hasPaidText( $feed ) );
	}

	public function getTheContentFeedDataProvider(): array {
		// フィードの種類
		// https://developer.wordpress.org/reference/functions/get_the_content_feed/#parameters
		return array(
			array( 'rss2' ),
			array( 'atom' ),
			array( 'rss' ),
			array( 'rdf' ),
		);
	}
}
