<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\GetBlockResult;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\BlockchainClientServiceImpl;
use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;

class BlockchainClientServiceImplTest extends UnitTestCaseBase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::resetDatabase(); // データベースをリセット
	}

	public function setUp(): void {
		parent::setUp();
		$this->chain_repository = self::container()->get( ChainRepository::class );
	}
	private ChainRepository $chain_repository;

	/**
	 * チェーンIDを取得するテスト
	 *
	 * @test
	 * @testdox [1C90A214] BlockchainClientServiceImpl::getChainID
	 */
	public function testGetChainID(): void {
		// ARRANGE
		$chain = $this->chain_repository->get( ChainID::privatenet1() );
		$sut   = new BlockchainClientServiceImpl( $chain );

		// ACT
		$result = $sut->getChainID();

		// ASSERT
		$this->assertInstanceOf( ChainID::class, $result );
		$this->assertEquals( ChainID::privatenet1(), $result );
	}


	/**
	 * ブロック番号を指定して`getBlockByNumber`を呼び出すテスト
	 *
	 * @test
	 * @testdox [CE3C07EF] BlockchainClientServiceImpl::getBlockByNumber - $block_number_or_tag
	 * @dataProvider getBlockByNumberDataProvider
	 * @param BlockNumber|BlockTag $block_number_or_tag
	 */
	public function testGetBlockByNumber( $block_number_or_tag ): void {
		// ARRANGE
		$chain = $this->chain_repository->get( ChainID::privatenet1() );
		$sut   = new BlockchainClientServiceImpl( $chain );

		// ACT
		$result = $sut->getBlockByNumber( $block_number_or_tag );

		// ASSERT
		$this->assertInstanceOf( GetBlockResult::class, $result );
		$this->assertInstanceOf( BlockNumber::class, $result->blockNumber() );
		$this->assertInstanceOf( UnixTimestamp::class, $result->timestamp() );
	}
	public function getBlockByNumberDataProvider(): array {
		return array(
			array( BlockNumber::from( 1 ) ),
			array( BlockTag::latest() ),
		);
	}
}
