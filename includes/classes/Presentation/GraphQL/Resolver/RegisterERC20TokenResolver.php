<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\SaveERC20Token;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

/**
 * ERC20トークンの情報をサーバーに登録します。
 */
class RegisterERC20TokenResolver extends ResolverBase {

	public function __construct(
		SaveERC20Token $save_erc20_token,
		UserAccessChecker $user_access_checker
	) {
		$this->save_erc20_token    = $save_erc20_token;
		$this->user_access_checker = $user_access_checker;
	}

	private SaveERC20Token $save_erc20_token;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_ID = new ChainID( $args['chainID'] );
		$address  = new Address( (string) $args['address'] );
		/** @var bool */
		$is_payable = $args['isPayable'] ?? null;

		// トークン情報を保存
		$this->save_erc20_token->handle( $chain_ID, $address, $is_payable );

		return true;
	}
}
