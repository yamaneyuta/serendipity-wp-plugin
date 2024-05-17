<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Web3;

use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Web3\IContract;
use Cornix\Serendipity\Core\Web3\Contract;

/**
 * コントラクトからのデータをキャッシュするクラス
 */
class CachedContract implements IContract {

	private const CACHE_EXPIRE = 60 * 60 * 1;   // 1時間

	public function __construct( int $chain_id ) {
		$this->contract = new Contract( $chain_id );
	}

	public function getChainId(): int {
		return $this->contract->getChainId(); }
	/** @var Contract */
	private $contract;

	/** @inheritdoc */
	public function getOracleLatestData( array $symbols ): array {
		$key_base = Constants::get( 'transientsKey.getOracleLatestDataBase' );

		$oracleData1 = get_transient( $key_base . $symbols[0] );
		$oracleData2 = get_transient( $key_base . $symbols[1] );

		$no_cached_symbols = array();
		if ( false === $oracleData1 ) {
			$no_cached_symbols[] = $symbols[0];
		}
		if ( false === $oracleData2 ) {
			$no_cached_symbols[] = $symbols[1];
		}
		if ( 0 === count( $no_cached_symbols ) ) {
			return array( $oracleData1, $oracleData2 );
		}

		//
		// 片方でもキャッシュが存在しない場合は、Oracleから最新のデータを取得する
		//

		// Oracleから最新のデータを取得
		$result = $this->contract->getOracleLatestData( $no_cached_symbols );

		foreach ( $result as $ret ) {
			// キャッシュを設定
			set_transient( $key_base . $ret->symbol, $ret, self::CACHE_EXPIRE );
		}

		return $this->getOracleLatestData( $symbols );
	}

	/** @inheritdoc */
	public function getSellableSymbolsInfo(): array {
		$result = $this->contract->getSellableSymbolsInfo();

		// 投稿編集画面からのアクセスのため、キャッシュは設定しない

		/** @var array $result */
		return $result;
	}

	public function getPayableSymbolsInfo(): array {
		$key = Constants::get( 'transientsKey.getPayableSymbolsInfoBase' ) . $this->contract->getChainId();

		$result = get_transient( $key );
		if ( false === $result ) {
			$result = $this->contract->getPayableSymbolsInfo();
			set_transient( $key, $result, 60 * 60 * 24 );
		}

		/** @var array $result */
		return $result;
	}
}
