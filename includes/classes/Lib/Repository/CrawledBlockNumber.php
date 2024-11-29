<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\BlockNumberType;

/**
 * 最後にクロールしたブロック番号を取得または保存するためのクラス
 */
class CrawledBlockNumber {
	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を取得します。
	 */
	public function get( int $chain_ID, string $block_tag ): ?BlockNumberType {
		assert( Judge::isChainID( $chain_ID ), "[2AAB831E] Invalid chain ID. - chain_ID: {$chain_ID}" );
		assert( Judge::isBlockTagName( $block_tag ), "[4306F5FA] Invalid block tag. - block_tag: {$block_tag}" );

		$block_number_hex = ( new OptionFactory() )->crawledBlockNumberHex( $chain_ID, $block_tag )->get();
		return is_null( $block_number_hex ) ? null : BlockNumberType::from( $block_number_hex );
	}

	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を保存します。
	 */
	public function set( int $chain_ID, string $block_tag, BlockNumberType $block_number ): bool {
		Judge::checkChainID( $chain_ID );
		Judge::checkBlockTagName( $block_tag );

		return ( new OptionFactory() )->crawledBlockNumberHex( $chain_ID, $block_tag )->update( $block_number->hex() );
	}
}
