<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

class SellingPostContentInfoType {
	public function __construct( int $character_count, int $image_count ) {
		if ( $character_count < 0 ) {
			throw new \InvalidArgumentException( '[EE3B5232] Invalid character count. - character_count: ' . $character_count );
		}
		if ( $image_count < 0 ) {
			throw new \InvalidArgumentException( '[545DC452] Invalid image count. - image_count: ' . $image_count );
		}

		$this->character_count = $character_count;
		$this->image_count     = $image_count;
	}

	/** 販売対象の投稿内容に含まれる文字数 */
	public int $character_count;

	/** 販売対象の投稿内容に含まれる画像数 */
	public int $image_count;
}
