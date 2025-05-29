<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Constants\Config;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Service\ChainService;

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

		// $confirmationsがnullの場合は、最低待機数を指定
		$confirmations = is_null( $confirmations ) ? Config::MIN_CONFIRMATIONS : $confirmations;
		// confirmationsが数値の文字列だった場合はint型に変換
		$confirmations = is_numeric( $confirmations ) ? (int) $confirmations : $confirmations;

		// confirmationsが正しい値かどうかをチェック
		Judge::checkConfirmations( $confirmations );

		// confirmationsを保存
		try {
			global $wpdb;
			$wpdb->query( 'START TRANSACTION' );
			// ChainDataのインスタンスを作成し、confirmationsを設定
			( new ChainService( $chain_ID, $wpdb ) )->setConfirmations( $confirmations );
			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		return true;
	}
}
