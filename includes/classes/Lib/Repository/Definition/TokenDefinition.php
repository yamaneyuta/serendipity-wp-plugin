<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Definition;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Types\Token;

class TokenDefinition {

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
			array( ChainID::ETH_MAINNET, 'BAT', '0x0D8775F648430679A709E98d2b0Cb6250d2887EF', 18 ),
			array( ChainID::ETH_MAINNET, 'LINK', '0x514910771AF9Ca656af840dff83E8264EcF986CA', 18 ),
			array( ChainID::ETH_MAINNET, 'MATIC', '0x7D1AfA7B718fb893dB30A3aBc0Cfc608AaCfeBB0', 18 ),
			array( ChainID::ETH_MAINNET, 'USDC', '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48', 6 ),

			// Sepolia
			array( ChainID::SEPOLIA, 'ETH', Ethers::zeroAddress(), 18 ),
			array( ChainID::SEPOLIA, 'LINK', '0x779877A7B0D9E8603169DdbD7836e478b4624789', 18 ),

			// Polygon zkEVMテストネット(L2/Sepolia)
			array( ChainID::POLYGON_ZK_EVM_CARDONA, 'ETH', Ethers::zeroAddress(), 18 ),

			// Soneiumテストネット(L2/Sepolia)
			array( ChainID::SONEIUM_MINATO, 'ETH', Ethers::zeroAddress(), 18 ),

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
	 * 指定したチェーンに存在するすべてのトークンを取得します。
	 */
	public function all( int $chain_ID ): array {
		/** @var Token[] */
		$tokens = array();
		foreach ( $this->token_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID ) {
				$tokens[] = new Token( $data[ self::CHAIN_ID_INDEX ], $data[ self::ADDRESS_INDEX ] );
			}
		}
		return $tokens;
	}

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

	/**
	 * 指定したチェーンID、シンボルのトークンの小数点以下桁数を取得します。
	 */
	public function decimals( int $chain_ID, string $symbol ): int {
		foreach ( $this->token_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID && $data[ self::SYMBOL_INDEX ] === $symbol ) {
				return $data[ self::DECIMALS_INDEX ];
			}
		}

		throw new \InvalidArgumentException( '[D342C006] decimals not found: ' . $chain_ID . ', ' . $symbol );
	}

	/**
	 * 指定した通貨シンボルの最大小数点以下桁数を取得します。
	 * ※ ネットワークを跨いだ比較を行い、最大値を取得します。
	 */
	public function maxDecimals( string $symbol ): int {
		/** @var int|null */
		$max = null;
		foreach ( $this->token_data as $data ) {
			if ( $data[ self::SYMBOL_INDEX ] === $symbol ) {
				if ( is_null( $max ) ) {
					$max = $data[ self::DECIMALS_INDEX ];
				} else {
					$max = max( $max, $data[ self::DECIMALS_INDEX ] );
				}
			}
		}

		if ( null === $max ) {
			throw new \InvalidArgumentException( '[35BAC9DE] max decimals not found: ' . $symbol );
		}
		return $max;
	}
}
