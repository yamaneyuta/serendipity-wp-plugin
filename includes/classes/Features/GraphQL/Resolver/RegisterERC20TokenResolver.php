<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Application\Service\TokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;

/**
 * ERC20トークンの情報をサーバーに登録します。
 */
class RegisterERC20TokenResolver extends ResolverBase {

	/**
	 * #[\Override]
	 */
	public function resolve( array $root_value, array $args ) {
		Validate::checkHasAdminRole();  // 管理者権限が必要

		/** @var int */
		$chain_ID = $args['chainID'];
		$address  = new Address( (string) $args['address'] );
		/** @var null|bool */
		$is_payable = $args['isPayable'] ?? null;

		if ( null === $address ) {
			throw new \InvalidArgumentException( '[B42FC6FA] Invalid address provided.' );
		} elseif ( ! is_bool( $is_payable ) ) {
			throw new \InvalidArgumentException( '[E80F8B39] isPayable must be a boolean value.' );
		}

		// トークン情報を保存
		( new TokenService() )->saveERC20Token( $chain_ID, $address, $is_payable );

		return true;
	}
}
