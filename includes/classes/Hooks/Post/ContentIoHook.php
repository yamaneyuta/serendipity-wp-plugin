<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Hooks\Post;

use Cornix\Serendipity\Core\Lib\Database\Schema\PaidContentTable;
use Cornix\Serendipity\Core\Lib\Repository\Name\BlockName;
use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes;
use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Strings\Strings;
use Cornix\Serendipity\Core\Types\NetworkCategory;
use Cornix\Serendipity\Core\Types\Price;

/**
 * 投稿内容を保存、または取得時のhooksを登録するクラス
 *
 * @package Cornix\Serendipity\Core\Hooks\Post
 */
class ContentIoHook {
	/**
	 * フックを登録します。
	 */
	public function register(): void {
		// 投稿を保存する際のフィルタを登録
		// `wp_insert_post_data`は、wp_postsに保存される投稿データを加工し、元の投稿内容を静的変数に保持。
		// `save_post`は`wp_insert_post_data`で保持した投稿内容から有料記事の情報をテーブルに保存する。
		add_filter( 'wp_insert_post_data', array( $this, 'wpInsertPostDataFilter' ), 10, 2 );
		add_filter( 'save_post', array( $this, 'savePostFilter' ), 10, 2 );

		// 投稿が削除された時のフックを登録
		add_action( 'delete_post', array( $this, 'deletePostAction' ), 10, 1 );

		// 投稿内容を取得する際のフィルタを登録
		// ※ Gutenbergでは`the_editor_content`が動作しないので`rest_prepare_post`(`rest_prepare_page`)を使用する
		// 　 https://github.com/WordPress/gutenberg/issues/12081#issuecomment-451631170
		// add_filter ( 'the_editor_content', array( $this, 'theEditorContentFilter' ), 10, 2 );
		add_filter( 'rest_prepare_post', array( $this, 'restPreparePostFilter' ), 10, 3 );
		add_filter( 'rest_prepare_page', array( $this, 'restPreparePageFilter' ), 10, 3 );
	}

	public function restPreparePostFilter( \WP_REST_Response $response, \WP_Post $post, \WP_REST_Request $request ): \WP_REST_Response {
		if ( ( new PaidContentTable() )->exists( $post->ID ) ) {
			// 有料記事の情報がある場合、
			// これは$response->data['content']['raw']に無料部分のみ格納された状態。
			// ここにウィジェットと有料部分を追加して返す。

			// 念のため投稿編集権限を持っていることを確認
			Judge::checkHasEditableRole();
			if ( ! ( new Access() )->canCurrentUserEditPost( $post->ID ) ) {
				throw new \LogicException( '[0196607A] You do not have permission to edit this post. - post ID: ' . $post->ID );
			}

			$selling_network_category_id = ( new PaidContentTable() )->getSellingNetworkCategoryID( $post->ID );
			$selling_price               = ( new PaidContentTable() )->getSellingPrice( $post->ID );
			assert( ! is_null( $selling_network_category_id ), "[E58341D9] Selling network category ID is null. - post ID: {$post->ID}" );
			assert( ! is_null( $selling_price ), "[8E5423E3] Selling price is null. - post ID: {$post->ID}" );

			$widget_content = ( new WidgetContentBuilder() )->build(
				$selling_network_category_id,
				$selling_price
			);
			$paid_content   = ( new PaidContentTable() )->getPaidContent( $post->ID ) ?? '';
			assert( ! is_null( $paid_content ), "[A1FF1B77] Paid content is null. - post ID: {$post->ID}" );

			$response->data['content']['raw']      = $response->data['content']['raw'] . "\n\n" . $widget_content . "\n\n" . $paid_content;
			$response->data['content']['rendered'] = apply_filters( 'the_content', $response->data['content']['raw'] );
		}

		return $response;
	}

	public function restPreparePageFilter( \WP_REST_Response $response, \WP_Post $post, \WP_REST_Request $request ): \WP_REST_Response {
		// ひとまず、ページも投稿と同じ処理を適用しておく
		// 個別の処理が必要な場合はここを変更して対応する
		return $this->restPreparePostFilter( $response, $post, $request );
	}

	/** 未保存の加工前投稿内容 */
	private static $unsaved_original_content = null;

	public function wpInsertPostDataFilter( array $data, array $postarr ): array {
		// 別のフックで有料記事部分の保存等を行えるように静的変数に投稿編集画面全体の内容を保持。
		// ※ ここでは投稿IDがまだ発行されていないため登録処理ができない。
		if ( is_null( self::$unsaved_original_content ) ) {
			// 投稿⇒リビジョンというように連続でここを通ったとき、2回目の$data['post_content']は無料部分だけになっている。
			// 加工後の投稿内容をオリジナルとして保持しないように、初回のみオリジナルの投稿内容を保持する。
			self::$unsaved_original_content = $data['post_content'] ?? null;
		}
		assert( ! is_null( self::$unsaved_original_content ), '[8EC62676] Unsaved original content is null.' );

		$divider = new RawContentDivider();
		if ( $divider->hasWidget( self::$unsaved_original_content ) ) {
			// ウィジェットが含まれている場合は、投稿内容を無料部分だけにして返す。
			// これにより、無料部分だけがwp_postsテーブルに保存されるようになる。
			$data['post_content'] = $divider->getFreeContent( self::$unsaved_original_content );
		}

		return $data;
	}

	public function savePostFilter( int $post_id, \WP_Post $post ): void {
		if ( is_null( self::$unsaved_original_content ) ) {
			throw new \LogicException( '[4F0E9951] Unsaved original content is null. - post ID: ' . $post_id );
		}

		// 最初に送信された投稿内容からウィジェットの属性を取得(nullの場合はウィジェットが含まれていない)
		$attributes = WidgetAttributes::fromContent( wp_unslash( self::$unsaved_original_content ) );
		if ( is_null( $attributes ) ) {
			// ウィジェットが含まれていない場合はウィジェットを削除して保存した可能性があるため、有料記事の情報を削除
			( new PaidContentTable() )->delete( $post_id );
		} else {
			// ウィジェットが含まれている場合は有料記事の情報を保存
			( new PaidContentTable() )->set(
				$post_id,
				( new RawContentDivider() )->getPaidContent( self::$unsaved_original_content ),
				$attributes->sellingNetworkCategory()->id(),
				$attributes->sellingPrice()
			);
		}
	}

	public function deletePostAction( int $post_id ): void {
		// 投稿が削除された時に有料記事の情報も削除
		( new PaidContentTable() )->delete( $post_id );
	}
}

class WidgetContentBuilder {
	public function build( int $selling_network_category_id, Price $selling_price ): string {
		$block_name = ( new BlockName() )->get();
		$attrs      = WidgetAttributes::from( NetworkCategory::from( $selling_network_category_id ), $selling_price->amountHex(), $selling_price->decimals(), $selling_price->symbol() )->toArray();
		$attrs_str  = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		return "<!-- wp:{$block_name} {$attrs_str} -->\n"
			. "<aside class=\"wp-block-create-block-qik-chain-pay ae6cefc4-82d4-4220-840b-d74538ea7284\"></aside>\n"
			. "<!-- /wp:{$block_name} -->";
	}
}

/**
 * `<!-- wp:XXX`が含まれる文字列(投稿内容)を分割します
 */
class RawContentDivider {
	public function __construct() {
		$block_name      = ( new BlockName() )->get();
		$this->start_tag = "<!-- wp:{$block_name}"; // start_tagにはプロパティが含まれるので`-->`は含めない
		$this->end_tag   = "<!-- /wp:{$block_name} -->";
	}
	private string $start_tag;
	private string $end_tag;

	/**
	 * 投稿内容にウィジェットが含まれているかどうかをチェックします。
	 *
	 * @param string $raw_content 投稿の生の内容
	 */
	public function hasWidget( string $content ): bool {
		Strings::strpos( $content, $this->start_tag );
		return ! is_null( $this->getWidgetStartPos( $content ) ) && ! is_null( $this->getPaidContentStartPos( $content ) );
	}

	/**
	 * 投稿の無料部分を取得します。
	 */
	public function getFreeContent( string $content ): ?string {
		$widget_start_pos = $this->getWidgetStartPos( $content );
		if ( null === $widget_start_pos ) {
			return null; // ウィジェットが配置されていない場合はnullを返す
		}
		return Strings::substr( $content, 0, $widget_start_pos ); // 無料部分はウィジェットの開始位置まで
	}

	/**
	 * 投稿のウィジェット部分を取得します。
	 */
	public function getWidgetContent( string $content ): ?string {
		$widget_start_pos = $this->getWidgetStartPos( $content );
		if ( null === $widget_start_pos ) {
			return null; // ウィジェットが配置されていない場合はnullを返す
		}
		$widget_end_tag_pos = Strings::strpos( $content, $this->end_tag, $widget_start_pos );
		if ( false === $widget_end_tag_pos ) {
			return null; // ウィジェットの終了タグが見つからない場合はnullを返す
		}
		return Strings::substr( $content, $widget_start_pos, $widget_end_tag_pos - $widget_start_pos + strlen( $this->end_tag ) ); // ウィジェットの内容を取得
	}

	/**
	 * 投稿の有料部分を取得します。
	 */
	public function getPaidContent( string $content ): ?string {
		$paid_content_start_pos = $this->getPaidContentStartPos( $content );
		if ( null === $paid_content_start_pos ) {
			return null; // 有料部分が配置されていない場合はnullを返す
		}
		return Strings::substr( $content, $paid_content_start_pos ); // 有料部分はウィジェットの終了位置から最後まで
	}

	/** ウィジェットの開始位置を取得します。 */
	private function getWidgetStartPos( string $content ): ?int {
		$start_pos = Strings::strpos( $content, $this->start_tag );
		if ( false === $start_pos ) {
			return null; // ウィジェットの開始タグが見つからない場合はnullを返す
		}
		return $start_pos;
	}

	/** 記事の有料部分開始位置を取得します。 */
	private function getPaidContentStartPos( string $content ): ?int {
		$widget_end_tag_pos = Strings::strpos( $content, $this->end_tag );
		if ( false === $widget_end_tag_pos ) {
			return null; // ウィジェットの終了タグが見つからない場合はnullを返す
		}
		return $widget_end_tag_pos + strlen( $this->end_tag ); // 終了タグの直後から有料部分が始まる
	}
}
