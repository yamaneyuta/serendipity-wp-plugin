<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\ClassName;
use Cornix\Serendipity\Core\Lib\Strings\Strings;

class SamplePostContent {
	public function __construct() {
		$this->class_name = ( new ClassName() )->getBlock();

		$this->free_text = 'FREE_FREE';    // 無料部分のテキスト
		$this->paid_text = 'PAID_PAID';    // 有料部分のテキスト
	}
	/** ブロックを配置した時に作成されるタグに付与されるCSSクラス名 */
	private string $class_name;
	/** 無料部分のテキスト */
	private string $free_text;
	/** 有料部分のテキスト */
	private string $paid_text;

	/**
	 * DBに格納される投稿内容のサンプルを取得します。
	 */
	public function get(): string {
		return <<<EOD
			<!-- wp:paragraph -->
			<p>{$this->free_text}</p>
			<!-- /wp:paragraph -->

			<!-- wp:create-block/todo-list {"dummy":"2024-08-26T10:54:22.901Z"} -->
			<aside class="wp-block-create-block-todo-list {$this->class_name}"></aside>
			<!-- /wp:create-block/todo-list -->

			<!-- wp:paragraph -->
			<p>{$this->paid_text}</p>
			<!-- /wp:paragraph -->
		EOD;
	}

	/**
	 * 投稿内容に無料部分のテキストが含まれているかどうかを取得します。
	 */
	public function hasFreeText( string $content ): bool {
		return Strings::strpos( $content, $this->free_text ) !== false;
	}

	/**
	 * 投稿内容にブロックが含まれているかどうかを取得します。
	 */
	public function hasBlock( string $content ): bool {
		return Strings::strpos( $content, $this->class_name ) !== false;
	}

	/**
	 * 投稿内容に有料部分のテキストが含まれているかどうかを取得します。
	 */
	public function hasPaidText( string $content ): bool {
		return Strings::strpos( $content, $this->paid_text ) !== false;
	}
}
