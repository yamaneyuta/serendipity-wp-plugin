<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Database\PostSetting;
use Cornix\Serendipity\Core\Lib\Repository\Database\TableName;
use Cornix\Serendipity\Core\Types\PostSettingType;
use Cornix\Serendipity\Core\Types\PriceType;

/**
 * 投稿設定のテスト
 */
class PostSettingTest extends IntegrationTestBase {

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
	 * データを登録してgetメソッドで取得できることを確認
	 *
	 * @test
	 * @testdox [31425B79] PostSetting::set & PostSetting::get - host: $host
	 * @dataProvider dbHostProvider
	 */
	public function setAndGet( string $host ) {
		$wpdb = WpdbFactory::create( $host );
		$this->initializeDatabase( $wpdb ); // データベース初期化
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost();  // 投稿を作成

		// 投稿に対する設定を登録
		$sut = new PostSetting( $wpdb );
		$sut->set( $post_ID, new PostSettingType( new PriceType( '0x1e8d8197', 18, 'ETH' ) ) );

		$data = $sut->get( $post_ID );

		// 登録したデータが取得できること
		$this->assertEquals( $data, new PostSettingType( new PriceType( '0x1e8d8197', 18, 'ETH' ) ) );
		// データが1件登録されている
		$this->assertEquals( 1, $this->recordCount( $wpdb ) );
	}

	/**
	 * データが登録されていない時にgetメソッドがnullを返すことを確認
	 *
	 * @test
	 * @testdox [6660CACE] PostSetting::get when no data - host: $host
	 * @dataProvider dbHostProvider
	 */
	public function getWhenNoData( string $host ) {
		$wpdb = WpdbFactory::create( $host );
		$this->initializeDatabase( $wpdb ); // データベース初期化
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost();  // 投稿を作成

		// 今作成した投稿に対する設定は作成せず、他の投稿に対する設定を作成する(誤って他のデータを取得していないことを確認)
		$sut             = new PostSetting( $wpdb );
		$another_post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost();
		$sut->set( $another_post_ID, new PostSettingType( new PriceType( '0x3adc7a7e', 18, 'ETH' ) ) );

		$data = $sut->get( $post_ID );

		// データが登録されていないのでnullが返ること
		$this->assertNull( $data );
	}

	/**
	 * データを2回登録した場合、最新のデータが取得できることを確認
	 *
	 * @test
	 * @testdox [D1D3D3D3] PostSetting::get when multiple data - host: $host
	 * @dataProvider dbHostProvider
	 */
	public function getWhenMultipleData( string $host ) {
		$wpdb = WpdbFactory::create( $host );
		$this->initializeDatabase( $wpdb ); // データベース初期化
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost();  // 投稿を作成

		$sut = new PostSetting( $wpdb );
		$sut->set( $post_ID, new PostSettingType( new PriceType( '0x20240629', 18, 'ETH' ) ) );
		usleep( 1000 ); // 1ミリ秒待機(ULIDの生成時間が同じにならないようにする)
		$sut->set( $post_ID, new PostSettingType( new PriceType( '0x20240630', 18, 'ETH' ) ) );

		$data = $sut->get( $post_ID );

		// 新しく登録したデータが取得できること
		$this->assertEquals( $data, new PostSettingType( new PriceType( '0x20240630', 18, 'ETH' ) ) );
		// レコードは2つ登録されている(上書きでなく、追加)
		$this->assertEquals( 2, $this->recordCount( $wpdb ) );
	}


	/**
	 * テーブルに登録されている件数を取得します。
	 *
	 * @param wpdb $wpdb
	 * @return int
	 */
	private function recordCount( wpdb $wpdb ): int {
		$table = TableName::postSettingHistory();
		$sql   = <<<SQL
			SELECT COUNT(*)
			FROM `$table`
		SQL;
		return (int) $wpdb->get_var( $sql );
	}

	public function dbHostProvider() {
		return ( new TestPattern() )->createDBHostMatrix();
	}
}
