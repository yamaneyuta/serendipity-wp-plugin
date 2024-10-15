<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;

class TokenData {

	private const CHAIN_ID_INDEX = 0;
	private const SYMBOL_INDEX   = 1;
	private const ADDRESS_INDEX  = 2;
	private const DECIMALS_INDEX = 3;

	public function __construct() {
		// ※ このデータから項目を削除する場合は、保存されているデータの整合性を確認する必要があります。
		// 例: 支払可能なトークン一覧に削除するものが含まれている可能性を考慮してアップグレード時に削除処理を実施する、など
		$this->token_data = array(
			// Mainnet
			array( ChainID::ETH_MAINNET, 'ETH', Ethers::zeroAddress(), 18 ),

			// Sepolia
			array( ChainID::SEPOLIA, 'ETH', Ethers::zeroAddress(), 18 ),

			// Privatenet L1
			array( ChainID::PRIVATENET_L1, 'ETH', Ethers::zeroAddress(), 18 ),
			array( ChainID::PRIVATENET_L1, 'TUSD', '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', 18 ),
			array( ChainID::PRIVATENET_L1, 'TJPY', '0xa513E6E4b8f2a923D98304ec87F64353C4D5C853', 18 ),

			// Privatenet L2
			array( ChainID::PRIVATENET_L2, 'MATIC', Ethers::zeroAddress(), 18 ),
			array( ChainID::PRIVATENET_L2, 'TUSD', '0x5FC8d32690cc91D4c39d9d3abcBD16989F875707', 18 ),
			array( ChainID::PRIVATENET_L2, 'TJPY', '0xa513E6E4b8f2a923D98304ec87F64353C4D5C853', 18 ),
		);
	}

	private array $token_data;

	/**
	 * 指定したチェーンID、アドレスのトークンが定義されているかどうかを取得します。
	 */
	public function exists( int $chain_ID, string $address ): bool {
		foreach ( $this->token_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID && $data[ self::ADDRESS_INDEX ] === $address ) {
				return true;
			}
		}

		return false;
	}

	public function symbol( int $chain_ID, string $address ): string {
		foreach ( $this->token_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID && $data[ self::ADDRESS_INDEX ] === $address ) {
				return $data[ self::SYMBOL_INDEX ];
			}
		}

		throw new \InvalidArgumentException( '[D342C006] symbol not found: ' . $chain_ID . ', ' . $address );
	}
}
