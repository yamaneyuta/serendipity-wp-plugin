<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\SaveERC20Token;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Factory\ChainRepositoryFactory;
use Cornix\Serendipity\Core\Infrastructure\Factory\TokenRepositoryFactory;

/**
 * ERC20トークンの情報をサーバーに登録します。
 */
class RegisterERC20TokenResolver extends ResolverBase {

	/**
	 * #[\Override]
	 */
	public function resolve( array $root_value, array $args ) {
		Validate::checkHasAdminRole();  // 管理者権限が必要

		$chain_ID = new ChainID( $args['chainID'] );
		$address  = new Address( (string) $args['address'] );
		/** @var bool */
		$is_payable = $args['isPayable'] ?? null;

		// トークン情報を保存
		$token_repository = ( new TokenRepositoryFactory() )->create();
		$chain_repository = ( new ChainRepositoryFactory() )->create();
		( new SaveERC20Token( $token_repository, $chain_repository ) )->handle( $chain_ID, $address, $is_payable );

		return true;
	}
}
