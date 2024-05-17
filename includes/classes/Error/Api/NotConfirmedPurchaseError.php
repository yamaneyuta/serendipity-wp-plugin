<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Error\Api;

class NotConfirmedPurchaseError extends \WP_Error {
	public function __construct( string $code ) {
		parent::__construct(
			$code,
			__( 'Could not confirm receipt.', 'todo-list' ),  // 購入が確認できませんでした。
			array( 'status' => 403 )    // data.status
		);
	}
}
