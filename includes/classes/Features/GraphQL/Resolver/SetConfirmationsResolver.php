<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\Confirmations;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class SetConfirmationsResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		// 管理者権限を持っているかどうかをチェック
		Judge::checkHasAdminRole();

		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var string|null */
		$confirmations = $args['confirmations'] ?? null;

		// confirmationsが数値の場合はint型に変換
		// ※nullでもブロックタグ名でもない場合は数値の文字列として扱う
		if ( ! is_null( $confirmations ) && ! Judge::isBlockTagName( $confirmations ) ) {
			$confirmations = (int) $confirmations;
		}

		// confirmationsを保存
		( new Confirmations() )->set( $chain_ID, $confirmations );

		return true;
	}
}
