<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Hook\Post;

use Cornix\Serendipity\Core\Entity\PaidContent;
use Cornix\Serendipity\Core\Lib\Convert\HtmlFormat;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\PaidContentTable;
use Cornix\Serendipity\Core\Repository\Environment;
use Cornix\Serendipity\Core\Repository\Name\BlockName;
use Cornix\Serendipity\Core\Repository\Name\ClassName;
use Cornix\Serendipity\Core\Repository\WidgetAttributes;
use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Lib\Strings\Strings;
use Cornix\Serendipity\Core\Service\PostService;

/**
 * 投稿内容を保存、または取得時のhooksを登録するクラス
 * ※ インストールしているテーマやプラグインの影響で`save_post`が
 *    2回呼び出されるようになったりするため、完全な対応は難しい。
 *    問題があれば都度対応することになると思われる。
 *
 * @package Cornix\Serendipity\Core\Hook\Post
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
		// [通常の画面]
		add_filter( 'the_content', array( $this, 'theContentFilter' ), 10, 1 );
		// [リビジョン画面表示]
		add_filter( '_wp_post_revision_field_post_content', array( $this, 'wpPostRevisionFieldPostContentFilter' ), 10, 4 );
		// [APIレスポンス]
		// ※ Gutenbergでは`the_editor_content`が動作しないので`rest_prepare_post`(`rest_prepare_page`)を使用する
		// 　 https://github.com/WordPress/gutenberg/issues/12081#issuecomment-451631170
		// add_filter ( 'the_editor_content', array( $this, 'theEditorContentFilter' ), 10, 2 );
		add_filter( 'rest_prepare_post', array( $this, 'restPreparePostFilter' ), 10, 3 );
		add_filter( 'rest_prepare_page', array( $this, 'restPreparePageFilter' ), 10, 3 );
	}

	/**
	 * ウィジェットの内容(ブロックタグ付きのHTML)を生成します。
	 */
	private function createWidgetContent( int $post_id ): string {
		return ( new WidgetContentBuilder() )->build( $post_id );
	}

	/**
	 * 投稿のREST APIレスポンスを加工します。
	 * このメソッドが呼び出されるタイミング:
	 *   - 投稿編集画面を開いたとき
	 *   - 投稿を保存した時
	 *   - wp/v2/posts等のAPIにアクセスした時
	 */
	public function restPreparePostFilter( \WP_REST_Response $response, \WP_Post $post, \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! ( new Access() )->canCurrentUserEditPost( $post->ID ) ) {
			return $response;   // 投稿の編集権限がない場合は何もしない
		}

		$paid_content = ( new PostService() )->get( $post->ID )->paidContent();
		if ( ! is_null( $paid_content ) ) {
			// このメソッドが呼び出されたタイミングでは$response->data['content']['raw']に無料部分のみ格納された状態。
			$free_content = $response->data['content']['raw'] ?? '';

			// この投稿の編集権限があり、かつ有料記事の情報が存在する場合はウィジェットと有料部分を結合して返す。
			$widget_content = $this->createWidgetContent( $post->ID );

			$full_content = $free_content . "\n\n" . $widget_content . "\n\n" . $paid_content->value();

			// レスポンスの内容を加工
			$response->data['content']['raw']      = $full_content;
			$response->data['content']['rendered'] = apply_filters( 'the_content', $full_content );
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

	/**
	 * 投稿の保存前のフック。投稿内容を加工してwp_postsに保存される内容を変更します。
	 */
	public function wpInsertPostDataFilter( array $data, array $postarr ): array {
		// 本文に何も記載していないとき、$data['post_content'] は false や空文字だったりしたためチェック(assert)は省略

		// どの画面から投稿が保存されようとしているのかを判定
		// ※ 通常の保存判定で`is_null( $_GET['action'] ?? null )`を含めているのは、テーマによって2回呼び出されることがあったため
		// 　 その時は2回目の呼び出しで`$_GET['action']`に`edit`が入っていた(WordPress標準のテーマは1回しか呼び出されない)
		$is_autosaving         = $postarr['post_type'] === 'revision' && defined( 'DOING_AUTOSAVE' );
		$is_revision_restoring = $postarr['post_type'] === 'post' && ( $_GET['action'] ?? null ) === 'restore' && is_numeric( $_GET['revision'] ?? null );
		$is_normal_saving      = $postarr['post_type'] === 'post' && is_null( $_GET['action'] ?? null ) && ! $is_autosaving && ! $is_revision_restoring;

		if ( $is_revision_restoring ) {
			// リビジョンからの復元の場合、$data['post_content']には無料部分しか入っていない。
			// 有料部分を含めた全体をオリジナルの投稿内容として保持する。
			$revision     = (int) $_GET['revision'];
			$free_content = $data['post_content'] ?? ''; // リビジョンからの復元の場合、ここは無料部分のみが入っている

			$paid_content = ( new PostService() )->get( $revision )->paidContent();   // リビジョンの有料部分を取得
			if ( ! is_null( $paid_content ) ) {
				// 有料部分が存在する場合は、ウィジェットと有料部分を結合して保持
				self::$unsaved_original_content =
					$free_content . "\n\n"
					. $this->createWidgetContent( $revision ) . "\n\n"
					. $paid_content->value();
			} else {
				// 有料部分が存在しない場合は無料部分のみを保持
				self::$unsaved_original_content = $free_content;
			}
		} elseif ( $is_normal_saving || $is_autosaving ) {
			// 通常の投稿編集画面からのリクエストの場合は、送信されたデータから投稿内容を取得
			self::$unsaved_original_content = $data['post_content'] ?? '';
			$divider                        = new RawContentDivider();
			if ( $divider->hasWidget( self::$unsaved_original_content ) ) {
				// ウィジェットが含まれている場合は、投稿内容を無料部分だけにして返す。
				// これにより、無料部分だけがwp_postsテーブルに保存されるようになる。
				$data['post_content'] = $divider->getFreeContent( self::$unsaved_original_content );
			}
		}

		return $data;
	}

	/**
	 * 投稿の保存後のフック。静的変数に保持した投稿内容から有料記事の情報を取得して保存します。
	 */
	public function savePostFilter( int $post_id, \WP_Post $post ): void {
		if ( is_null( self::$unsaved_original_content ) ) {
			// 投稿内容が未保存の場合は何もしない(ゴミ箱に移動された時などが該当)
			return;
		}

		// 最初に送信された投稿内容からウィジェットの属性を取得(nullの場合はウィジェットが含まれていない)
		$attributes   = WidgetAttributes::fromContent( wp_unslash( self::$unsaved_original_content ) );
		$post_service = new PostService();
		if ( is_null( $attributes ) ) {
			// ウィジェットが含まれていない場合はウィジェットを削除して保存した可能性があるため、有料記事の情報を削除
			$post_service->deletePaidContent( $post_id );
		} else {
			$paid_content_text = ( new RawContentDivider() )->getPaidContent( wp_unslash( self::$unsaved_original_content ) );
			assert( ! is_null( $paid_content_text ), '[2B9ADC9A] Paid content is null. - post_id: ' . $post_id );
			// ウィジェットが含まれている場合は有料記事の情報を保存
			$paid_content = PaidContent::from( $paid_content_text );
			$post_service->savePaidContent(
				$post_id,
				$paid_content,
				$attributes->sellingNetworkCategory(),
				$attributes->sellingPrice()
			);
		}
	}

	/**
	 * 投稿が削除された時のアクション。有料記事の情報も削除します。
	 */
	public function deletePostAction( int $post_id ): void {
		// テスト実行中、テストツールによって投稿が削除される。
		// その際、このフックが呼び出されるが、テーブル作成前に呼び出されるとエラーになるため
		// テスト中かつテーブルが存在しない場合は何もしない
		if ( ( new Environment() )->isTesting() && ! ( new PaidContentTable( $GLOBALS['wpdb'] ) )->exists() ) {
			return; // テスト中に限り、テーブルが存在しない場合は何もしない
		}

		// 投稿が削除された時に有料記事の情報も削除
		( new PostService() )->deletePaidContent( $post_id );
	}

	/**
	 * 投稿の内容をフィルタします。
	 * 投稿、固定ページの内容に有料記事のウィジェットを追加します。
	 */
	public function theContentFilter( string $content ): string {
		// 投稿、固定ページ以外は処理抜け
		if ( ! is_single() && ! is_page() ) {
			return $content;
		}

		global $post;
		/** @var int|null */
		$post_id = isset( $post->ID ) ? $post->ID : null;
		assert( is_int( $post_id ), '[97CAA15C] Post ID is not an integer. - ' . json_encode( $post_id ) );

		// 有料記事の情報がある場合はウィジェットを結合して返す
		$paid_content = ( new PostService() )->get( $post_id )->paidContent();
		if ( ! is_null( $paid_content ) ) {
			// HTMLコメントを除去したウィジェットを追加
			$content .= "\n\n" . HtmlFormat::removeHtmlComments( $this->createWidgetContent( $post_id ) );
		}

		return $content;
	}

	/**
	 * リビジョン画面で表示される投稿内容をフィルタします。
	 * 差分は投稿全体で比較したいので、リビジョンの内容に有料記事のウィジェットと有料部分を追加します。
	 */
	public function wpPostRevisionFieldPostContentFilter( string $revision_field_content, string $field, \WP_Post $revision_post, string $context ) {
		$post_id      = $revision_post->ID;
		$paid_content = ( new PostService() )->get( $post_id )->paidContent();

		if ( ! is_null( $paid_content ) ) {
			// 記事の有料部分の情報がある場合はウィジェットと有料部分を結合して返す
			$widget_content          = $this->createWidgetContent( $post_id );
			$revision_field_content .= "\n\n" . $widget_content . "\n\n" . $paid_content->value(); // ウィジェットと有料部分を追加
		}
		return $revision_field_content;
	}
}

class WidgetContentBuilder {
	public function build( int $post_id ): string {
		$post_data  = ( new PostService() )->get( $post_id );
		$block_name = ( new BlockName() )->get();
		$attrs      = WidgetAttributes::from( $post_data->sellingNetworkCategory(), $post_data->sellingPrice() )->toArray();
		$attrs_str  = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		$class_name = ( new ClassName() )->getBlock();
		return "<!-- wp:{$block_name} {$attrs_str} -->\n"
			. "<aside class=\"wp-block-create-block-qik-chain-pay {$class_name}\"></aside>\n"
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
