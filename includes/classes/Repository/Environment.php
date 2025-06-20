<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Lib\Option\OptionFactory;

/**
 * インストールされている環境から情報を取得するクラス。
 * マシンに配置されているファイルやインストール済みのデータベースなど、実行環境によって異なる情報を取得する場合に使用します。
 */
class Environment {

	/**
	 * 開発モードかどうかを取得します。
	 *
	 * 通常操作において、以下の状態の場合はtrueを返します。
	 * - localhost:8888等、開発用WordPressへアクセスしている時
	 * - テスト(phpunit)実行時
	 *
	 * また、以下の状態の場合はfalseを返します。
	 * - 本番環境での運用時(zipファイルからインストールした場合)
	 */
	public function isDevelopmentMode(): bool {
		$option = ( new OptionFactory() )->isDevelopmentMode();
		/** @var bool|null */
		$is_development_mode = $option->get( null );

		if ( is_null( $is_development_mode ) ) {
			$package_json_path   = Config::ROOT_DIR . '/package.json';
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
