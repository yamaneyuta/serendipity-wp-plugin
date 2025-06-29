<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestLib\Util;

use Cornix\Serendipity\TestLib\Entity\WpUser;

/**
 * 指定したユーザーに切り替えるためのユーティリティクラス
 *
 * インスタンスが生成されると、コンストラクタで指定されたユーザーに切り替わります。
 * インスタンスが破棄されると、元のユーザーに戻ります。
 *
 * ※ C++のstd::lock_guardのような使い方を想定しています。
 */
class WithUser {

	public function __construct( WpUser $user ) {
		// インスタンス生成時に指定されたユーザーに切り替える
		$this->prev_user = WpUser::current();
		$user->setCurrent();
	}

	public function __destruct() {
		// コンストラクタで設定したユーザーに戻す
		$this->prev_user->setCurrent();
	}

	private WpUser $prev_user;
}
