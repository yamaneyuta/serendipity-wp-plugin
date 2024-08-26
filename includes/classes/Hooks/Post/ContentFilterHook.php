<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Post;

use Cornix\Serendipity\Core\Lib\Post\ContentFilter;
use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Lib\Security\Assert;
use Cornix\Serendipity\Core\Lib\Strings\Strings;

class ContentFilterHook {

	public function register(): void {
		// 投稿内容取得のフィルタを登録
		add_filter( 'the_content', array( $this, 'addFilterTheContent' ) );
	}

	public function addFilterTheContent( string $content ): string {
		// ※ 投稿編集画面から保存を行った場合も、レスポンスを返すためにこのメソッドを通過する。
		// この時、アクセス元はAPI(`/wp/v2/posts/{id}`等)であるため、`get_current_screen`等の関数は使えないことに注意。

		// 現在の投稿IDを取得
		$post_ID = get_the_ID();
		Assert::isPostID( $post_ID );

		if ( ! $this->shouldFilterContent( $post_ID ) ) {
			Assert::false( is_feed(), '[B63A1B4B] should not be feed' );

			// フィルタしない場合はそのまま返す
			return $content;
		} elseif ( is_singular() ) {
			// is_singular: 以下の3つのいずれかの時
			// 　　1. is_single: 投稿ページ・添付ページ・カスタム投稿タイプの個別ページ
			// 　　2. is_page: 固定ページ
			// 　　3. is_attachment: 添付ページ
			Assert::false( is_feed(), '[A4A5E6E7] should not be feed' );

			$contentFilter = new ContentFilter( $content );
			$free          = $contentFilter->getFree();  // 無料部分を取得

			if ( null === $free ) {
				// 無料部分が取得できない場合はウィジェットが配置されていない場合なので$contentをそのまま返す。
				return $content;
			} else {
				$widget = $contentFilter->getWidget();  // ウィジェットを取得
				Assert::true( is_string( $widget ), '[4F2720BC] Invalid content. - content: ' . $content );
				// 無料部分が取得できた場合は、ウィジェットを追加して返す。
				return $free . $widget;
			}
		} else {
			// その他。以下の場合を含む。
			// - フィード(RSS, ATOM等): is_feed() === true
			// 　- 記事一覧が表示されるページ
			// 　- /wp-json/wp/v2/posts へアクセス(GET)された時

			// 無料部分のみを返す。
			$contentFilter = new ContentFilter( $content );
			$free          = $contentFilter->getFree();  // 無料部分を取得

			// 無料部分が取得できない場合はウィジェットが配置されていない場合なので$contentをそのまま返す。
			return null === $free ? $content : $free;
		}
	}

	/**
	 * 投稿内容をフィルタするかどうかを取得します。
	 *
	 * @return bool
	 */
	private function shouldFilterContent( int $post_ID ): bool {
		$access = new Access();
		if ( false === $access->canCurrentUserEditPost( $post_ID ) ) {
			// 対象の投稿を編集できないユーザーによるアクセスの場合、フィルタが必要
			return true;
		}

		// 以下、投稿を編集可能なユーザーがアクセスしている状態

		global $pagenow;
		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
			// 投稿編集画面からのアクセスの場合、フィルタ不要
			return false;
		}

		// WP REST APIのリクエストで投稿を保存する時は、フィルタ不要(投稿を保存した時のレスポンスを返すため)
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) { // 投稿編集画面から保存する時はPOST
			$request_uri = $_SERVER['REQUEST_URI'];
			if (
				Strings::starts_with( $request_uri, '/wp-json/wp/v2/posts/' ) // パーマリンクをデフォルトから変更した場合
				|| Strings::starts_with( $request_uri, '/index.php?rest_route=%2Fwp%2Fv2%2Fposts%2F' )    // パーマリンクをデフォルトのまま使用している場合
			) {
				return false;
			}
		}

		// その他の場合、フィルタが必要
		return true;
	}
}
