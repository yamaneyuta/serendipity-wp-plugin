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
		// 個別ページでは無料部分とブロックが表示され、有料部分は表示されないことを確認
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
}
