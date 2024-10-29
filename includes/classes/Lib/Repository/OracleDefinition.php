<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Types\NetworkCategory;
use Cornix\Serendipity\Core\Types\SymbolPair;

class OracleDefinition {

	private const CHAIN_ID_INDEX     = 0;
	private const BASE_SYMBOL_INDEX  = 1;
	private const QUOTE_SYMBOL_INDEX = 2;
	private const ADDRESS_INDEX      = 3;

	public function __construct() {
		$this->oracle_data = array(
			array( ChainID::ETH_MAINNET, 'AUD', 'USD', '0x77F9710E7d0A19669A13c055F62cd80d313dF022' ),
			array( ChainID::ETH_MAINNET, 'ETH', 'USD', '0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419' ),
			array( ChainID::ETH_MAINNET, 'EUR', 'USD', '0xb49f677943BC038e9857d61E7d053CaA2C1734C1' ),
			array( ChainID::ETH_MAINNET, 'GBP', 'USD', '0x5c0Ab2d9b5a7ed9f470386e82BB36A3613cDd4b5' ),
			array( ChainID::ETH_MAINNET, 'JPY', 'USD', '0xBcE206caE7f0ec07b545EddE332A47C2F75bbeb3' ),

			array( ChainID::SEPOLIA, 'AUD', 'USD', '0xB0C712f98daE15264c8E26132BCC91C40aD4d5F9' ),
			array( ChainID::SEPOLIA, 'ETH', 'USD', '0x694AA1769357215DE4FAC081bf1f309aDC325306' ),
			array( ChainID::SEPOLIA, 'EUR', 'USD', '0x1a81afB8146aeFfCFc5E50e8479e826E7D55b910' ),
			array( ChainID::SEPOLIA, 'GBP', 'USD', '0x91FAB41F5f3bE955963a986366edAcff1aaeaa83' ),
			array( ChainID::SEPOLIA, 'JPY', 'USD', '0x8A6af2B75F23831ADc973ce6288e5329F63D86c6' ),

			array( ChainID::PRIVATENET_L1, 'ETH', 'USD', '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512' ),
			array( ChainID::PRIVATENET_L1, 'JPY', 'USD', '0x9fE46736679d2D9a65F0992F2272dE9f3c7fa6e0' ),
			array( ChainID::PRIVATENET_L1, 'MATIC', 'USD', '0xDc64a140Aa3E981100a9becA4E685f962f0cF6C9' ),
		);
	}
	private array $oracle_data;

	/**
	 * 指定したチェーンIDとアドレスの通貨ペアを取得します
	 */
	public function symbolPair( int $chain_ID, string $address ): ?SymbolPair {
		foreach ( $this->oracle_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID && $data[ self::ADDRESS_INDEX ] === $address ) {
				return new SymbolPair( $data[ self::BASE_SYMBOL_INDEX ], $data[ self::QUOTE_SYMBOL_INDEX ] );
			}
		}
		return null;
	}

	/**
	 * 指定した通貨ペアのOracleがデプロイされているチェーンID一覧を取得します。
	 *
	 * @return int[]
	 */
	public function chainIDs( SymbolPair $symbol_pair ): array {
		$chain_IDs = array();
		foreach ( $this->oracle_data as $data ) {
			if ( $data[ self::BASE_SYMBOL_INDEX ] === $symbol_pair->base() && $data[ self::QUOTE_SYMBOL_INDEX ] === $symbol_pair->quote() ) {
				$chain_IDs[] = $data[ self::CHAIN_ID_INDEX ];
			}
		}
		return $chain_IDs;
	}

	/**
	 * 指定したチェーン、通貨ペアのOracleコントラクトのアドレスを取得します。
	 */
	public function address( int $chain_ID, SymbolPair $symbol_pair ): ?string {
		foreach ( $this->oracle_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID && $data[ self::BASE_SYMBOL_INDEX ] === $symbol_pair->base() && $data[ self::QUOTE_SYMBOL_INDEX ] === $symbol_pair->quote() ) {
				return $data[ self::ADDRESS_INDEX ];
			}
		}
		return null;
	}

	/**
	 * 指定したネットワークカテゴリに存在するOracleのシンボル(`XXX/USD`の`XXX`部分)一覧を取得します。
	 *
	 * @return string[]
	 * @deprecated Oracleを使用するネットワークが複数存在する可能性があること、クォート通貨がUSD以外も存在する可能性があることから、このメソッドは非推奨です。
	 */
	public function getSymbols( NetworkCategory $network_category ): array {
		$chain_ID = ( new NetworkCategoryDefinition() )->getOracleChainID( $network_category );
		/** @var string[] */
		$symbols = array();
		foreach ( $this->oracle_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID ) {
				$symbols[] = $data[ self::BASE_SYMBOL_INDEX ];
			}
		}
		return $symbols;
	}
}
