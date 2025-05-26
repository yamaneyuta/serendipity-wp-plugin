<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Convert\HtmlFormat;
use Cornix\Serendipity\Core\Lib\Database\Schema\PaidContentTable;
use Cornix\Serendipity\Core\Lib\Post\ContentAnalyzer;

class SellingContentResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// 有料部分のコンテンツを取得
		$paid_content = ( new PaidContentTable() )->getPaidContent( $post_ID );

		// 有料部分のコンテンツが取得できなかった場合はnullを返す
		if ( null === $paid_content ) {
			return null;
		}

		// HTMLコメントを除去
		$paid_content = HtmlFormat::removeHtmlComments( $paid_content );

		// 有料部分のコンテンツの文字数と画像数を取得
		$analyzer        = new ContentAnalyzer( $paid_content );
		$character_count = $analyzer->getCharacterCount();
		$image_count     = $analyzer->getImageCount();

		return array(
			'characterCount' => $character_count,
			'imageCount'     => $image_count,
		);
	}
}
