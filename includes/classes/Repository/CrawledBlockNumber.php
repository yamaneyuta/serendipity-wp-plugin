<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Lib\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\ValueObject\ChainID;

/**
 * 最後にクロールしたブロック番号を取得または保存するためのクラス
 */
class CrawledBlockNumber {
	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を取得します。
	 */
	public function get( ChainID $chain_ID, string $block_tag ): ?BlockNumber {
		assert( Validate::isBlockTagName( $block_tag ), "[4306F5FA] Invalid block tag. - block_tag: {$block_tag}" );

		$block_number_hex = ( new OptionFactory() )->crawledBlockNumberHex( $chain_ID, $block_tag )->get();
		return is_null( $block_number_hex ) ? null : BlockNumber::from( $block_number_hex );
	}

	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を保存します。
	 */
	public function set( ChainID $chain_ID, string $block_tag, BlockNumber $block_number ): void {
		Validate::checkBlockTagName( $block_tag );

		( new OptionFactory() )->crawledBlockNumberHex( $chain_ID, $block_tag )->update( $block_number->hex() );
	}
}
