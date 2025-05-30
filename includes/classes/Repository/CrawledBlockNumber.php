<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Lib\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;

/**
 * 最後にクロールしたブロック番号を取得または保存するためのクラス
 */
class CrawledBlockNumber {
	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を取得します。
	 */
	public function get( int $chain_ID, string $block_tag ): ?BlockNumber {
		assert( Judge::isChainID( $chain_ID ), "[2AAB831E] Invalid chain ID. - chain_ID: {$chain_ID}" );
		assert( Judge::isBlockTagName( $block_tag ), "[4306F5FA] Invalid block tag. - block_tag: {$block_tag}" );

		$block_number_hex = ( new OptionFactory() )->crawledBlockNumberHex( $chain_ID, $block_tag )->get();
		return is_null( $block_number_hex ) ? null : BlockNumber::from( $block_number_hex );
	}

	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を保存します。
	 */
	public function set( int $chain_ID, string $block_tag, BlockNumber $block_number ): void {
		Judge::checkChainID( $chain_ID );
		Judge::checkBlockTagName( $block_tag );

		( new OptionFactory() )->crawledBlockNumberHex( $chain_ID, $block_tag )->update( $block_number->hex() );
	}
}
