<?php
declare(strict_types=1);

/**
 * 抜粋のフィルターを行うフックのテスト
 */
class ExcerptFilterHookTest extends IntegrationTestBase {

	/**
	 * トップページ(投稿一覧)で抜粋を取得したときに、有料部分が含まれないことを確認する
	 *
	 * @test
	 * @testdox [7495BF61][Hooks] ExcerptFilterHookTest - topPage, post_excerpt is empty
	 */
	public function topPage() {
		// ARRANGE
		$samplePostContent = new SamplePostContent();
		// 投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => $samplePostContent->get(),
				'post_excerpt' => '',   // `post_excerpt`を指定しない場合、自動的に`Post excerpt 0000056`のような値が設定される
			)
		);
		// トップページへ移動
		$this->go_to( '/' );

		// ACT
		// 抜粋を取得
		$excerpt = get_the_excerpt( $post_ID );

		// ASSERT
		// トップページでは無料部分の文字列だけ取得できることを確認
		$this->assertTrue( $samplePostContent->hasFreeText( $excerpt ) );
		$this->assertFalse( $samplePostContent->hasBlock( $excerpt ) );
		$this->assertFalse( $samplePostContent->hasPaidText( $excerpt ) );
	}


	/**
	 * 抜粋がユーザーによって入力されている時、トップページ(投稿一覧)で抜粋を取得したときに、入力された抜粋が取得できることを確認する
	 *
	 * @test
	 * @testdox [E8646C97][Hooks] ExcerptFilterHookTest - topPage, post_excerpt is not empty
	 */
	public function topPageAndSetExcerpt() {
		// ARRANGE
		$samplePostContent = new SamplePostContent();
		// 投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => $samplePostContent->get(),
				'post_excerpt' => 'EXCERPT_EXCERPT',    // 適当な抜粋を指定
			)
		);
		// トップページへ移動
		$this->go_to( '/' );

		// ACT
		// 抜粋を取得
		$excerpt = get_the_excerpt( $post_ID );

		// ASSERT
		// 投稿内容ではなく、ユーザーが入力した抜粋が取得できることを確認
		$this->assertFalse( $samplePostContent->hasFreeText( $excerpt ) );
		$this->assertFalse( $samplePostContent->hasBlock( $excerpt ) );
		$this->assertFalse( $samplePostContent->hasPaidText( $excerpt ) );
		$this->assertEquals( 'EXCERPT_EXCERPT', $excerpt );
	}


	/**
	 * 投稿ページで抜粋を取得したときに、有料部分が含まれないことを確認する
	 *
	 * @test
	 * @testdox [290572FD][Hooks] ExcerptFilterHookTest - permalink, post_excerpt is empty
	 */
	public function permalinkAndSetExcerpt() {
		// ARRANGE
		$samplePostContent = new SamplePostContent();
		// 投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => $samplePostContent->get(),
				'post_excerpt' => '',   // `post_excerpt`を指定しない場合、自動的に`Post excerpt 0000056`のような値が設定される
			)
		);
		// 投稿ページへ移動
		$this->go_to( get_permalink( $post_ID ) );

		// ACT
		// 抜粋を取得
		$excerpt = get_the_excerpt( $post_ID );

		// ASSERT
		// 投稿ページでは無料部分の文字列だけ取得できることを確認
		$this->assertTrue( $samplePostContent->hasFreeText( $excerpt ) );
		$this->assertFalse( $samplePostContent->hasBlock( $excerpt ) );
		$this->assertFalse( $samplePostContent->hasPaidText( $excerpt ) );
	}


	/**
	 * 抜粋がユーザーによって入力されている時、投稿ページで抜粋を取得したときに、入力された抜粋が取得できることを確認する
	 *
	 * @test
	 * @testdox [5B168821][Hooks] ExcerptFilterHookTest - permalink, post_excerpt is not empty
	 */
	public function permalink() {
		// ARRANGE
		$samplePostContent = new SamplePostContent();
		// 投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => $samplePostContent->get(),
				'post_excerpt' => 'EXCERPT_EXCERPT',    // 適当な抜粋を指定
			)
		);
		// 投稿ページへ移動
		$this->go_to( get_permalink( $post_ID ) );

		// ACT
		// 抜粋を取得
		$excerpt = get_the_excerpt( $post_ID );

		// ASSERT
		// 投稿内容ではなく、ユーザーが入力した抜粋が取得できることを確認
		$this->assertFalse( $samplePostContent->hasFreeText( $excerpt ) );
		$this->assertFalse( $samplePostContent->hasBlock( $excerpt ) );
		$this->assertFalse( $samplePostContent->hasPaidText( $excerpt ) );
		$this->assertEquals( 'EXCERPT_EXCERPT', $excerpt );
	}
}
