<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ChainService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;

class SetConfirmationsResolver extends ResolverBase {

	public function __construct(
		ChainService $chain_service,
		UserAccessChecker $user_access_checker
	) {
		$this->chain_service       = $chain_service;
		$this->user_access_checker = $user_access_checker;
	}

	private ChainService $chain_service;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_ID = new ChainID( $args['chainID'] );
		/** @var string|null */
		$confirmations_input = $args['confirmations'] ?? null;

		// Confirmationsオブジェクトを作成
		$confirmations = Confirmations::from( is_null( $confirmations_input ) ? Config::MIN_CONFIRMATIONS : $confirmations_input );

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
