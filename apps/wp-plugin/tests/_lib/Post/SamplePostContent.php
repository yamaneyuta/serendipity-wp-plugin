<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Repository\Name\ClassName;
use Cornix\Serendipity\Core\Lib\Strings\Strings;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;

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
	public function get( ?NetworkCategoryID $selling_network_category_id = null, ?Price $selling_price = null ): string {
		$selling_network_category_id       = $selling_network_category_id ?? NetworkCategoryID::privatenet(); // 指定されなかった場合はプライベートネット
		$selling_network_category_id_value = $selling_network_category_id->value();
		$selling_amount_value              = $selling_price ? $selling_price->amount()->value() : '1000'; // 指定されなかった場合は1000(=0x3e8)
		$selling_symbol                    = $selling_price ? $selling_price->symbol() : 'JPY'; // 指定されなかった場合はJPY
		return <<<EOD
			<!-- wp:paragraph -->
			<p>{$this->free_text}</p>
			<!-- /wp:paragraph -->

			<!-- wp:create-block/qik-chain-pay {"sellingNetworkCategoryID":{$selling_network_category_id_value},"sellingAmount":"{$selling_amount_value}","sellingSymbol":"{$selling_symbol}"} -->
			<aside class="wp-block-create-block-qik-chain-pay {$this->class_name}"></aside>
			<!-- /wp:create-block/qik-chain-pay -->

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
