<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\PluginMainFile;

class Activation {

	public function __construct() {
		// プラグインが有効化された時に実行されるフック。
		// 第一引数はプラグインのメインファイルとなるファイルのパスを指定する必要がある。
		// https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/
		register_activation_hook( PluginMainFile::getPath(), array( $this, 'register_activation_hook' ) );
	}


	/**
	 * プラグインが有効化された時に実行されるフック。
	 *
	 * プラグインアップデート時は呼び出されないことに注意。
	 * https://wordpress.stackexchange.com/questions/39813/register-activation-hook-and-updating
	 */
	public function register_activation_hook(): void {

		// `Ulid`ライブラリが64bit以上でないと動作しない。
		if ( PHP_INT_SIZE < 8 ) {
			throw new \Exception( '{E2348BFC-2603-479B-B398-639CBC1049D9}' );
		}
	}
}
