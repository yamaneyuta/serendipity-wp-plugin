<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Factory\TokenServiceFactory;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

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
		/** @var null|bool */
		$is_payable = $args['isPayable'] ?? null;

		if ( null === $address ) {
			throw new \InvalidArgumentException( '[B42FC6FA] Invalid address provided.' );
		} elseif ( ! is_bool( $is_payable ) ) {
			throw new \InvalidArgumentException( '[E80F8B39] isPayable must be a boolean value.' );
		}

		// トークン情報を保存
		( new TokenServiceFactory() )->create()->saveERC20Token( $chain_ID, $address, $is_payable );

		return true;
	}
}
