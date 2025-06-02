<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Repository\PayableTokens;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\Addresses;

class RemovePayableTokensResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var Addresses */
		$remove_token_addresses = Addresses::fromAddressValues( $args['tokenAddresses'] );

		Validate::checkHasAdminRole(); // 管理者権限が必要

		// 更新が不要な場合は処理抜け
		if ( empty( $remove_token_addresses ) ) {
			return true;    // 特に意味のない戻り値
		}

		// 保存済みのトークンオブジェクトの配列を取得
		$current_payable_tokens = ( new PayableTokens() )->get( $chain_ID );

		// 削除しようとしているアドレスが保存されていない場合は例外をスロー
		$current_payable_tokens_addresses = Addresses::from(
			array_map(
				fn( $token ) => $token->address(),
				$current_payable_tokens
			)
		);
		foreach ( $remove_token_addresses as $remove_token_address ) {
			if ( ! $current_payable_tokens_addresses->contains( $remove_token_address ) ) {
				throw new \InvalidArgumentException( '[B7367B4B] Token not found: ' . (string) $remove_token_address );
			}
		}

		// 保存するトークンオブジェクトの配列を作成
		$new_payable_tokens = array_values(
			array_filter(
				$current_payable_tokens,
				fn( $token ) => $remove_token_addresses->contains( $token->address() ) === false
			)
		);
		assert( count( $new_payable_tokens ) === count( $current_payable_tokens ) - count( $remove_token_addresses->toArray() ) );

		// データを更新
		( new PayableTokens() )->save( $chain_ID, $new_payable_tokens );

		return true;    // 特に意味のない戻り値
	}
}
