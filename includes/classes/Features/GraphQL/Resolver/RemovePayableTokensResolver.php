<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class RemovePayableTokensResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var string[] */
		$token_addresses = $args['tokenAddresses'];

		Judge::checkHasAdminRole(); // 管理者権限が必要

		// 更新が不要な場合は処理抜け
		if ( empty( $token_addresses ) ) {
			return true;    // 特に意味のない戻り値
		}

		// 保存済みのトークンオブジェクトの配列を取得
		$current_payable_tokens = ( new PayableTokens() )->get( $chain_ID );

		// 削除しようとしているアドレスが保存されていない場合は例外をスロー
		$current_payable_tokens_addresses = array_map(
			fn( $token ) => $token->address(),
			$current_payable_tokens
		);
		foreach ( $token_addresses as $address ) {
			if ( ! in_array( $address, $current_payable_tokens_addresses ) ) {
				throw new \InvalidArgumentException( '[B7367B4B] Token not found: ' . $address );
			}
		}

		// 保存するトークンオブジェクトの配列を作成
		$new_payable_tokens = array_filter(
			$current_payable_tokens,
			fn( $token ) => ! in_array( $token->address(), $token_addresses )
		);
		assert( count( $new_payable_tokens ) === count( $current_payable_tokens ) - count( $token_addresses ) );

		// データを更新
		( new PayableTokens() )->save( $chain_ID, $new_payable_tokens );

		return true;    // 特に意味のない戻り値
	}
}
