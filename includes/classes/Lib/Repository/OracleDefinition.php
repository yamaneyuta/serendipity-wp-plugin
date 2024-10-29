<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class OracleDefinition {

	private const CHAIN_ID_INDEX = 0;
	private const SYMBOL_INDEX   = 1;
	private const ADDRESS_INDEX  = 2;

	public function __construct() {
		$mainnet_chain_ID      = ChainID::ETH_MAINNET;
		$testnet_chain_ID      = ChainID::SEPOLIA;
		$privatenetL1_chain_ID = ChainID::PRIVATENET_L1;

		$oracle_data_json = <<<JSON
			{
				"data": [
					[ {$mainnet_chain_ID}, "AUD", "0x77F9710E7d0A19669A13c055F62cd80d313dF022" ],
					[ {$mainnet_chain_ID}, "ETH", "0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419" ],
					[ {$mainnet_chain_ID}, "EUR", "0xb49f677943BC038e9857d61E7d053CaA2C1734C1" ],
					[ {$mainnet_chain_ID}, "GBP", "0x5c0Ab2d9b5a7ed9f470386e82BB36A3613cDd4b5" ],
					[ {$mainnet_chain_ID}, "JPY", "0xBcE206caE7f0ec07b545EddE332A47C2F75bbeb3" ],

					[ {$testnet_chain_ID}, "AUD", "0xB0C712f98daE15264c8E26132BCC91C40aD4d5F9" ],
					[ {$testnet_chain_ID}, "ETH", "0x694AA1769357215DE4FAC081bf1f309aDC325306" ],
					[ {$testnet_chain_ID}, "EUR", "0x1a81afB8146aeFfCFc5E50e8479e826E7D55b910" ],
					[ {$testnet_chain_ID}, "GBP", "0x91FAB41F5f3bE955963a986366edAcff1aaeaa83" ],
					[ {$testnet_chain_ID}, "JPY", "0x8A6af2B75F23831ADc973ce6288e5329F63D86c6" ],

					[ {$privatenetL1_chain_ID}, "ETH", "0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512" ],
					[ {$privatenetL1_chain_ID}, "JPY", "0x9fE46736679d2D9a65F0992F2272dE9f3c7fa6e0" ],
					[ {$privatenetL1_chain_ID}, "MATIC", "0xDc64a140Aa3E981100a9becA4E685f962f0cF6C9" ]
				]
			}
		JSON;

		$this->oracle_data = json_decode( $oracle_data_json, true )['data'];
	}
	private array $oracle_data;


	/**
	 * 指定したネットワークカテゴリに存在するOracleのシンボル(`XXX/USD`の`XXX`部分)一覧を取得します。
	 *
	 * @return string[]
	 */
	public function getSymbols( NetworkCategory $network_category ): array {
		$chain_ID = ( new NetworkCategoryDefinition() )->getOracleChainID( $network_category );
		/** @var string[] */
		$symbols = array();
		foreach ( $this->oracle_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID ) {
				$symbols[] = $data[ self::SYMBOL_INDEX ];
			}
		}
		return $symbols;
	}
}
