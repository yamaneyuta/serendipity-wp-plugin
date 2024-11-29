<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;

/**
 * 最後にクロールしたブロック番号を取得または保存するためのクラス
 */
class CrawledBlockNumber {
	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を取得します。
	 */
	public function get( int $chain_ID, string $block_tag ): ?string {
		assert( Judge::isChainID( $chain_ID ), "[2AAB831E] Invalid chain ID. - chain_ID: {$chain_ID}" );
		assert( Judge::isBlockTagName( $block_tag ), "[4306F5FA] Invalid block tag. - block_tag: {$block_tag}" );

		return ( new OptionFactory() )->crawledBlockNumberHex( $chain_ID, $block_tag )->get();
	}

	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を保存します。
	 */
	public function set( int $chain_ID, string $block_tag, string $block_number_hex ): bool {
		Judge::checkHex( $block_number_hex );
		Judge::checkBlockTagName( $block_tag );
		Judge::checkHex( $block_number_hex );

		return ( new OptionFactory() )->crawledBlockNumberHex( $chain_ID, $block_tag )->update( $block_number_hex );
	}
}
