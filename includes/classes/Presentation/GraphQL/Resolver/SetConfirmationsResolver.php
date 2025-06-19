<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ChainService;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class SetConfirmationsResolver extends ResolverBase {

	public function __construct( ChainService $chain_service ) {
		$this->chain_service = $chain_service;
	}

	private ChainService $chain_service;

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		// 管理者権限を持っているかどうかをチェック
		Validate::checkHasAdminRole();

		$chain_ID = new ChainID( $args['chainID'] );
		/** @var string|null */
		$confirmations = $args['confirmations'] ?? null;

		// $confirmationsがnullの場合は、最低待機数を指定
		$confirmations = is_null( $confirmations ) ? Config::MIN_CONFIRMATIONS : $confirmations;
		// confirmationsが数値の文字列だった場合はint型に変換
		$confirmations = is_numeric( $confirmations ) ? (int) $confirmations : $confirmations;

		// confirmationsが正しい値かどうかをチェック
		Validate::checkConfirmations( $confirmations );

		// confirmationsを保存
		try {
			global $wpdb;
			$wpdb->query( 'START TRANSACTION' );
			$this->chain_service->saveConfirmations( $chain_ID, $confirmations );
			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		return true;
	}
}
