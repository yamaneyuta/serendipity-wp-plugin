<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Repository\PayableTokens;
use Cornix\Serendipity\Core\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\Address;

class AddPayableTokensResolver extends ResolverBase {

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

		Validate::checkHasAdminRole(); // 管理者権限が必要

		// 更新が不要な場合は処理抜け
		if ( empty( $token_addresses ) ) {
			return true;    // 特に意味のない戻り値
		}

		// 保存済みのトークンオブジェクトの配列を取得
		$current_payable_tokens = ( new PayableTokens() )->get( $chain_ID );

		// 追加しようとしているアドレスがすでに保存済み場合は例外をスロー
		foreach ( $token_addresses as $address ) {
			foreach ( $current_payable_tokens as $current_payable_token ) {
				if ( $current_payable_token->address()->equals( new Address( $address ) ) ) {
					throw new \InvalidArgumentException( '[734CC2DE] Token already exists: ' . $address );
				}
			}
		}

		// 追加するトークンオブジェクトの配列を作成
		$add_tokens = array_map(
			fn ( $token_address ) => ( new TokenData() )->get( $chain_ID, $token_address ),
			$token_addresses
		);

		// データを更新
		( new PayableTokens() )->save( $chain_ID, array_merge( $current_payable_tokens, $add_tokens ) );

		return true;    // 特に意味のない戻り値
	}
}
