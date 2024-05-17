<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Access\Access;
use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Posts\ContentTrimer;
use Cornix\Serendipity\Core\Utils\Strings;

class ContentFilter {
	public function __construct() {
		// 投稿内容取得のフィルタを登録
		add_filter( 'the_content', array( $this, 'add_filter_the_content' ) );

		// 抜粋のフィルタを登録
		add_filter( 'get_the_excerpt', array( $this, 'add_filter_get_the_excerpt' ) );

		// 投稿へのパーマリンクフィルタ(アフィリエイト用に変更するフィルタを登録)
		add_filter( 'the_permalink_rss', array( $this, 'add_filter_the_permalink_rss' ) );
	}

	public function add_filter_the_content( string $content ): string {
		if ( current_user_can( 'edit_posts' ) && Access::isPostEditScreenReferer() ) {
			// ログイン状態のユーザーがedit_postsの権限を持っており、かつ、リファラが投稿編集画面の時は、内容をそのまま返す。
			// 補足: Gutenberg -> 新規投稿の初回保存時はここを通る。投稿編集時の更新時は通らない。
			return $content;
		} elseif ( is_singular() ) {
			// is_singular: 以下の3つのいずれかの時
			// 　　1. is_single: 投稿ページ・添付ページ・カスタム投稿タイプの個別ページ
			// 　　2. is_page: 固定ページ
			// 　　3. is_attachment: 添付ページ
			return ContentTrimer::trimForView( $content );
		} else {
			// その他。以下の場合を含む。
			// - フィード(RSS, ATOM等): is_feed() === true
			// - メインクエリ: is_main_query() === true
			// 　- 記事一覧が表示されるページ
			// 　- /wp-json/wp/v2/posts でアクセスされた時

			// 無料部分のみを返す。
			return ContentTrimer::trimOnlyFree( $content );
		}
	}


	function add_filter_get_the_excerpt(): string {

		// 投稿情報を取得
		$post = get_post();

		// 記事投稿画面の「抜粋」を入力している場合は、その値をそのまま使用
		if ( is_string( $post->post_excerpt ) && Strings::strlen( $post->post_excerpt ) ) {
			return $post->post_excerpt;
		}

		// 投稿内容を取得
		$content = apply_filters( 'the_content', $post->post_content );

		// 記事の無料エリアを取得
		$post_content_html = ContentTrimer::trimOnlyFree( $content );

		// 以下、HTMLタグから抜粋を作成。
		// → wp-includes > formattting.php > wp_trim_excerpt 内の処理をここで実施
		$post_content_html = strip_shortcodes( $post_content_html );
		$post_content_html = excerpt_remove_blocks( $post_content_html );
		$post_content_html = apply_filters( 'the_content', $post_content_html );
		$post_content_html = str_replace( ']]>', ']]&gt;', $post_content_html );
		$excerpt_length    = (int) _x( '55', 'excerpt_length' );
		$excerpt_length    = (int) apply_filters( 'excerpt_length', $excerpt_length );
		$excerpt_more      = apply_filters( 'excerpt_more', ' [&hellip;]' );

		return wp_trim_words( $post_content_html, $excerpt_length, $excerpt_more );
	}

	public function add_filter_the_permalink_rss( string $post_permalink ): string {
		// リクエストがRSSやATOM等のフィード取得でない場合は、そのまま返す。
		// 　　is_feed:
		// 　　https://developer.wordpress.org/themes/basics/conditional-tags/#a-syndication
		if ( ! is_feed() ) {
			return $post_permalink;
		}

		//
		// TODO: アンテナサイトへの登録が行われていない場合は、そのまま返す。
		//

		// アフィリエイターのウォレットアドレスを取得するためのキー。
		$key = Constants::get( 'affiliateWalletUrlParameterKey' );
		// `get_query_var`は`$wp->add_query_var`で登録したキーを指定する必要があるため、`$_GET`で代用。
		$affiliate_wallet = isset( $_GET[ $key ] ) ? $_GET[ $key ] : null;

		// アフィリエイターのウォレットアドレスが取得できない場合は、そのまま返す。
		if ( true !== is_string( $affiliate_wallet ) || 1 !== preg_match( '/^0x[a-fA-F0-9]{40}$/', $affiliate_wallet ) ) {
			return $post_permalink;
		}

		//
		// 記事にウィジェットが配置されているか確認するための投稿IDを取得
		$post_ID = url_to_postid( $post_permalink );
		if ( 0 === $post_ID ) {
			// 通常、ここは通らない。
			Logger::error( '{FCA1D9A4-E96E-489E-ADC7-6DF5D4D1F4F6}' );
			return $post_permalink;
		}

		// ウィジェットが配置されていない場合は、そのまま返す。
		if ( false === Strings::strpos( get_post( $post_ID )->post_content, Constants::get( 'className.widget' ) ) ) {
			return $post_permalink;
		}

		// 投稿のURLにアフィリエイターのウォレットアドレスを付与して返す。
		$query = parse_url( $post_permalink, PHP_URL_QUERY );
		return $post_permalink . ( $query ? '&' : '?' ) . $key . '=' . $affiliate_wallet;
	}
}
