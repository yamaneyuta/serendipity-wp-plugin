<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Infrastructure\Content\HtmlContentAnalyzer;

/** 記事の有料部分を表現するクラス */
class PaidContent {
	private function __construct( string $paid_content_text ) {
		$this->content_text = $paid_content_text;
		$this->analyzer     = new HtmlContentAnalyzer( $paid_content_text );
	}
	private string $content_text;
	private HtmlContentAnalyzer $analyzer;

	public static function from( string $paid_content_text ): self {
		return new self( $paid_content_text );
	}

	public function text(): string {
		return $this->content_text;
	}

	/** 有料部分の文字数 */
	public function characterCount(): int {
		return $this->analyzer->getCharacterCount();
	}

	/** 有料部分の画像数 */
	public function imageCount(): int {
		return $this->analyzer->getImageCount();
	}

	public function __toString(): string {
		return $this->content_text;
	}
}
