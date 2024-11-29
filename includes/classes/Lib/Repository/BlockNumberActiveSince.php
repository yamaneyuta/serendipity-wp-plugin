<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\BlockNumberType;

/**
 * 指定したチェーンが最初に有効になった(≒取引が開始された)ブロック番号を取得するためのクラス
 * ※ このブロック番号からイベントを取得すれば、サイトでの取引全てが取得できる
 */
class BlockNumberActiveSince {
	/**
	 * 指定したチェーンが最初に有効になった(≒取引が開始された)ブロック番号を取得します。
	 */
	public function get( int $chain_ID ): ?BlockNumberType {
		$block_number_hex = ( new OptionFactory() )->activeSinceBlockNumberHex( $chain_ID )->get();
		assert( is_null( $block_number_hex ) || Judge::isHex( $block_number_hex ), "[FF97B758] Invalid block number. - block_number_hex: {$block_number_hex}" );
		return is_null( $block_number_hex ) ? null : BlockNumberType::from( $block_number_hex );
	}

	/**
	 * 指定したチェーンが最初に有効になった(≒取引が開始された)ブロック番号を設定します。
	 */
	public function set( int $chain_ID, BlockNumberType $block_number ): bool {
		if ( ! is_null( $this->get( $chain_ID ) ) ) {
			// 上書きしない
			throw new \InvalidArgumentException( "[FBE35625] active start block number is already set. chain_ID: {$chain_ID}" );
		}

		return ( new OptionFactory() )->activeSinceBlockNumberHex( $chain_ID )->update( $block_number->hex() );
	}
}
