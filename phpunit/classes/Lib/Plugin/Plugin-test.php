<?php

use Cornix\Serendipity\Core\Lib\Plugin\Plugin;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

class PluginTest extends TestCase {

	protected function set_up() {
		parent::set_up();
		// Your own additional setup.
	}

	protected function tear_down() {
		// Your own additional tear down.
		parent::tear_down();
	}

	/**
	 * @test
	 * @testdox [8A04D33B] プラグインのバージョンを取得する
	 */
	public function version() {
		$version = Plugin::version();
		// ここはバージョンアップのたびに書き換える必要あり
		$versions = array(
			'0.0.0',    // 前回のバージョン
			'1.0.0',    // 今回のバージョン
		);

		// 上記定義が最新化されていること
		$this->assertEquals( $versions[1], $version );

		// package.jsonに記載のバージョンと一致していること
		$this->assertEquals( $this->packageJsonVersion(), $version );

		// 前回のバージョンよりも今回のバージョンが大きいこと
		// ※ WordPressはPHPの`version_compare`を使用してバージョンの比較を行う。
		// https://developer.wordpress.org/plugins/plugin-basics/header-requirements/#notes
		$this->assertGreaterThan( 0, version_compare( $versions[1], $versions[0] ) );
	}

	/**
	 * package.jsonに記載のバージョンを取得します。
	 *
	 * @return string
	 */
	private function packageJsonVersion(): string {
		$package_json = file_get_contents( __DIR__ . '/../../../../package.json' );
		$package_json = json_decode( $package_json, true );
		$version      = $package_json['version'];
		assert( is_string( $version ) && strlen( $version ) > 0 );
		return $version;
	}

	/**
	 * @test
	 * @testdox []
	 */
	public function textDomain() {
		$text_domain = Plugin::textDomain();
		// 文字列の長さは0より大きい
		$this->assertGreaterThan( 0, strlen( $text_domain ) );
	}
}
