<?php

use Cornix\Serendipity\Core\Lib\Algorithm\Sort\VersionSorter;
use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

class VersionSorterTest extends WP_UnitTestCase {

	// #[\Override]
	public function setUp(): void {
		parent::setUp();
		// Your own additional setup.
	}

	// #[\Override]
	public function tearDown(): void {
		// Your own additional tear down.
		parent::tearDown();
	}

	/**
	 * @test
	 * @testdox [9FC57D05] VersionSorter::sort()
	 * @dataProvider provideSort
	 */
	public function sort( $data, $expected ) {

		$sut = new VersionSorter();

		$sorted = $sut->sort( $data );

		$this->assertEquals( $expected, $sorted );
		$this->assertEquals( ( new PluginInfo() )->version(), end( $sorted ) );
	}

	public function provideSort() {
		$versions = array( '1.0.0', '0.8.0', '0.0.0' ); // 昇順に並べ直すので昇順にならないように宣言。現在のプラグインバージョンを必ず入れること。
		$expected = array( '0.0.0', '0.8.0', '1.0.0' );

		$this->assertEquals( ( new PluginInfo() )->version(), end( $expected ) ); // プラグインのバージョンをは最後になることを確認

		$data_set = array();

		for ( $i = 0; $i < 10; $i++ ) {
			$data = $versions;
			shuffle( $data );
			$data_set[] = array( $data, $expected );
		}

		return $data_set;
	}
}
