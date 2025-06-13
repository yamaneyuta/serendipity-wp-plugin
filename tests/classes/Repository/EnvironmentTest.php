<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Features\Uninstall\OptionUninstaller;
use Cornix\Serendipity\Core\Repository\Environment;
use Cornix\Serendipity\Core\Lib\Option\OptionFactory;

class EnvironmentTest extends WP_UnitTestCase {
	/**
	 * テスト時は開発モードがtrueであることを確認
	 * ※ ファイルパス依存のためテストを実施
	 *
	 * @test
	 * @testdox [F66DBAF5] Environment::isDevelopmentMode
	 */
	public function testGet() {
		// ARRANGE
		( new OptionUninstaller() )->execute();
		$option = ( new OptionFactory() )->isDevelopmentMode();
		$this->assertNull( $option->get( null ) );  // テスト実行前はoptionsテーブルにデータが存在しないことを確認

		// ACT
		$is_development_mode = ( new Environment() )->isDevelopmentMode();

		// ASSERT
		// テスト時は package.json が存在するため、常に true が返る
		$this->assertTrue( $is_development_mode );
		// optionsテーブルにもtrueが保存されていることを確認
		$this->assertTrue( $option->get( null ) );
		// 再度呼び出してもtrueが返ることを確認
		$this->assertTrue( ( new Environment() )->isDevelopmentMode() );
	}
}
