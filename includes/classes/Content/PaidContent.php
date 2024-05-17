<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Content;

use Cornix\Serendipity\Core\Posts\ContentTrimer;
use Cornix\Serendipity\Core\Utils\Strings;

class PaidContent {

	// コンストラクタ
	public function __construct( int $post_id ) {
		$this->filteredPaidContent = $this->getFilteredPaidContent( $post_id );
	}


	/** @var string */
	private $filteredPaidContent;

	/**
	 * 有料記事の内容を`the_content`フィルタを通した状態で取得します。
	 */
	private function getFilteredPaidContent( int $post_id ): string {
		// フィルタを通す前の投稿内容を取得
		$non_filtered_content = get_post( $post_id )->post_content;
		// フィルタを通す前の有料記事部分を取得
		$non_filtered_paid_content = ContentTrimer::trimOnlyPaid( $non_filtered_content );

		// 有料記事部分を`the_content`フィルタを通して返す。
		return apply_filters( 'the_content', $non_filtered_paid_content );
	}


	/**
	 * 有料記事の内容を取得します。
	 *
	 * @return string
	 */
	public function getContent(): string {
		return $this->filteredPaidContent;
	}


	/**
	 * 有料記事部分の文字数を取得します。
	 */
	public function getCharactersNum(): int {

		$content = $this->filteredPaidContent;

		// 改行を除去
		$content = str_replace( array( "\r\n", "\r", "\n" ), '', $content );

		// blockquote（引用）を除去（引用は書いた文字数としてカウントしない）
		$pattern = '/<blockquote.*?<\/blockquote>/';
		$content = preg_replace( $pattern, '', $content );

		// HTMLのタグを除去
		$content = wp_strip_all_tags( $content );
		preg_match( $pattern, $content, $matches );

		// 空白を削除
		$content = str_replace( array( ' ', '　' ), '', $content );

		// 文字数を返却
		return Strings::strlen( $content );
	}



	/**
	 * 有料記事部分に含まれる画像（img）タグの数を取得します。
	 */
	public function getImageTagsNum(): int {
		$img_tag_matches = null;
		preg_match_all( '/<img("[^"]*"|\'[^\']*\'|[^\'">])*>/', $this->filteredPaidContent, $img_tag_matches );
		return count( $img_tag_matches[0] );
	}
}
