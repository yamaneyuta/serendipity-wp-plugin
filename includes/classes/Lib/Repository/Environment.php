<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;

/**
 * インストールされている環境から情報を取得するクラス。
 * マシンに配置されているファイルやインストール済みのデータベースなど、実行環境によって異なる情報を取得する場合に使用します。
 */
class Environment {

	/**
	 * 開発モードかどうかを取得します。
	 */
	public function isDevelopmentMode(): bool {
		$option = ( new OptionFactory() )->isDevelopmentMode();
		/** @var bool|null */
		$is_development_mode = $option->get( null );

		if ( is_null( $is_development_mode ) ) {
			$package_json_path   = __DIR__ . '/../../../../package.json';
			$is_development_mode = file_exists( $package_json_path );
			$option->update( $is_development_mode );    // '0'や'1'のような文字列で保存される
		}

		return $is_development_mode;
	}

	/**
	 * ユニットテスト実施中かどうかを取得します。
	 */
	public function isTesting(): bool {
		return 'testing' === getenv( 'APP_ENV' );
	}
}
