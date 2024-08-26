<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Post;

class ExcerptFilterHook {

	public function register() {
		// 以下の結果から、`get_the_excerpt`を用いて抜粋のフィルタを実装する。
		//
		// 凡例    [数字]:呼び出し順  -:呼び出されない
		// | アクセス               | get_the_excerpt | the_excerpt | the_excerpt_rss |
		// |------------------------|-----------------|-------------|-----------------|
		// | 管理画面記事一覧       | 1               | -           | -               |
		// | 投稿編集画面           | 1               | 2           | -               |
		// | トップページ(記事一覧) | -               | -           | -               |
		// | `/?feed=xxx`           | 1               | -           | 2               |
		// | `/wp-json/wp/v2/posts` | 1               | 2           | -               |

		add_filter( 'get_the_excerpt', array( $this, 'addFilterGetTheExcerpt' ) );
	}

	public function addFilterGetTheExcerpt( string $excerpt ): string {
		// 投稿内容を取得
		// ※ ここで`./ContentFilterHook`で登録したフィルタが適用される
		$the_content = apply_filters( 'the_content', get_the_content( '', false, get_post() ) );

		// `wp_trim_excerpt`相当の処理を行った結果を返す
		return $this->trimExcerpt( $the_content );
	}

	/**
	 * `wp_trim_excerpt`相当の処理を行います。
	 *
	 * 参考:
	 * https://wordpress.stackexchange.com/questions/37618/apply-filters-and-the-excerpt-are-giving-unexpected-results#answer-37659
	 *
	 * `wp_trim_excerpt`のソースコード:
	 * https://github.com/WordPress/wordpress-develop/blob/830d66c55cdb5afcaa8c0137d0c0f991258565ee/src/wp-includes/formatting.php#L3956-L4029
	 *
	 * @return string 指定した`$text`に対して`wp_trim_excerpt`相当の処理を行った結果
	 */
	private function trimExcerpt( string $text ): string {
		// phpcs:disable
		$raw_excerpt = $text;

		// if ( '' === trim( $text ) ) {    // delete
		{                                   // add
			// $post = get_post( $post );                   // delete
			// $text = get_the_content( '', false, $post ); // delete

			$text = strip_shortcodes( $text );
			$text = excerpt_remove_blocks( $text );
			// $text = excerpt_remove_footnotes( $text );														// delete
			$text = function_exists( 'excerpt_remove_footnotes' ) ? excerpt_remove_footnotes( $text ) : $text;	// add

			/*
			 * Temporarily unhook wp_filter_content_tags() since any tags
			 * within the excerpt are stripped out. Modifying the tags here
			 * is wasteful and can lead to bugs in the image counting logic.
			 */
			$filter_image_removed = remove_filter( 'the_content', 'wp_filter_content_tags', 12 );

			/*
			 * Temporarily unhook do_blocks() since excerpt_remove_blocks( $text )
			 * handles block rendering needed for excerpt.
			 */
			$filter_block_removed = remove_filter( 'the_content', 'do_blocks', 9 );

			/** This filter is documented in wp-includes/post-template.php */
			$text = apply_filters( 'the_content', $text );
			$text = str_replace( ']]>', ']]&gt;', $text );

			// Restore the original filter if removed.
			if ( $filter_block_removed ) {
				add_filter( 'the_content', 'do_blocks', 9 );
			}

			/*
			 * Only restore the filter callback if it was removed above. The logic
			 * to unhook and restore only applies on the default priority of 10,
			 * which is generally used for the filter callback in WordPress core.
			 */
			if ( $filter_image_removed ) {
				add_filter( 'the_content', 'wp_filter_content_tags', 12 );
			}

			/* translators: Maximum number of words used in a post excerpt. */
			$excerpt_length = (int) _x( '55', 'excerpt_length' );

			/**
			 * Filters the maximum number of words in a post excerpt.
			 *
			 * @since 2.7.0
			 *
			 * @param int $number The maximum number of words. Default 55.
			 */
			$excerpt_length = (int) apply_filters( 'excerpt_length', $excerpt_length );

			/**
			 * Filters the string in the "more" link displayed after a trimmed excerpt.
			 *
			 * @since 2.9.0
			 *
			 * @param string $more_string The string shown within the more link.
			 */
			$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			$text         = wp_trim_words( $text, $excerpt_length, $excerpt_more );
		}

		/**
		 * Filters the trimmed excerpt string.
		 *
		 * @since 2.8.0
		 *
		 * @param string $text        The trimmed text.
		 * @param string $raw_excerpt The text prior to trimming.
		 */
		return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
		// phpcs:enable
	}
}
