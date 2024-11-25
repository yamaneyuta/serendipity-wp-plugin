<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\Oracle;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Types\SymbolPair;

class OracleEthMainnetDefinition extends OracleDefinitionBase {

	private const BASE_SYMBOL_INDEX          = 0;
	private const QUOTE_SYMBOL_INDEX         = 1;
	private const ADDRESS_INDEX              = 2;
	private const BASE_SYMBOL_CATEGORY_INDEX = 3;

	/** @inheritdoc */
	public function chainID(): int {
		return ChainID::ETH_MAINNET;
	}

	private function data() {
		// 定義内容はテストファイルを参照。
		return array(
			array( 'rswETH', 'ETH', '0xb613CfebD0b6e95abDDe02677d6bC42394FdB857', 'Crypto' ),
			array( 'FIL', 'ETH', '0x0606Be69451B1C9861Ac6b3626b99093b713E801', 'Crypto' ),
			array( 'FDUSD', 'USD', '0xfAA9147190c2C2cc5B8387B4f49016bDB3380572', 'Crypto' ),
			array( 'UNI', 'ETH', '0xD6aA3D25116d8dA79Ea0246c4826EB951872e02e', 'Crypto' ),
			array( 'USDT', 'ETH', '0xEe9F2375b4bdF6387aa8265dD4FB8F16512A1d46', 'Crypto' ),
			array( 'BAT', 'ETH', '0x0d16d4528239e9ee52fa531af613AcdB23D88c94', 'Crypto' ),
			array( 'USDT', 'USD', '0x3E7d1eAB13ad0104d2750B8863b489D65364e32D', 'Crypto' ),
			array( 'SUSHI', 'ETH', '0xe572CeF69f43c2E488b33924AF04BDacE19079cf', 'Crypto' ),
			array( 'KNC', 'USD', '0xf8fF43E991A81e6eC886a3D281A2C6cC19aE70Fc', 'Crypto' ),
			array( 'AVAX', 'USD', '0xFF3EEb22B5E3dE6e705b44749C2559d704923FD7', 'Crypto' ),
			array( 'PERP', 'ETH', '0x3b41D5571468904D4e53b6a8d93A6BaC43f02dC9', 'Crypto' ),
			array( 'CBETH', 'ETH', '0xF017fcB346A1885194689bA23Eff2fE6fA5C483b', 'Crypto' ),
			array( 'COMP', 'ETH', '0x1B39Ee86Ec5979ba5C322b826B3ECb8C79991699', 'Crypto' ),
			array( 'COMP', 'USD', '0xdbd020CAeF83eFd542f4De03e3cF0C28A4428bd5', 'Crypto' ),
			array( 'KRW', 'USD', '0x01435677FB11763550905594A16B645847C1d0F3', 'Fiat' ),
			array( 'USDC', 'ETH', '0x986b5E1e1755e3C2440e960477f25201B0a8bbD4', 'Crypto' ),
			array( 'STETH', 'USD', '0xCfE54B5cD566aB89272946F602D76Ea879CAb4a8', 'Crypto' ),
			array( 'BAL', 'USD', '0xdF2917806E30300537aEB49A7663062F4d1F2b5F', 'Crypto' ),
			array( '1INCH', 'ETH', '0x72AFAECF99C9d9C8215fF44C77B94B99C28741e8', 'Crypto' ),
			array( 'MAVIA', 'USD', '0x29d26C008e8f201eD0D864b1Fd9392D29d0C8e96', 'Crypto' ),
			array( 'LINK', 'ETH', '0xDC530D9457755926550b59e8ECcdaE7624181557', 'Crypto' ),
			array( 'AAVE', 'ETH', '0x6Df09E975c830ECae5bd4eD9d90f3A95a4f88012', 'Crypto' ),
			array( 'ZRX', 'ETH', '0x2Da4983a622a8498bb1a21FaE9D8F6C664939962', 'Crypto' ),
			array( 'LUSD', 'USD', '0x3D7aE7E594f2f2091Ad8798313450130d0Aba3a0', 'Crypto' ),
			array( 'TAO', 'USD', '0x1c88503c9A52aE6aaE1f9bb99b3b7e9b8Ab35459', 'Crypto' ),
			array( 'AUD', 'USD', '0x77F9710E7d0A19669A13c055F62cd80d313dF022', 'Fiat' ),
			array( 'PYUSD', 'USD', '0x8f1dF6D7F2db73eECE86a18b4381F4707b918FB1', 'Crypto' ),
			array( 'XCN', 'USD', '0xeb988B77b94C186053282BfcD8B7ED55142D3cAB', 'Crypto' ),
			array( 'RSR', 'USD', '0x759bBC1be8F90eE6457C44abc7d443842a976d02', 'Crypto' ),
			array( 'ALCX', 'ETH', '0x194a9AaF2e0b67c35915cD01101585A33Fe25CAa', 'Crypto' ),
			array( 'BTC', 'USD', '0xF4030086522a5bEEa4988F8cA5B36dbC97BeE88c', 'Crypto' ),
			array( 'GRT', 'ETH', '0x17D054eCac33D91F7340645341eFB5DE9009F1C1', 'Crypto' ),
			array( 'LRC', 'ETH', '0x160AC928A16C93eD4895C2De6f81ECcE9a7eB7b4', 'Crypto' ),
			array( 'YFI', 'USD', '0xA027702dbb89fbd58938e4324ac03B58d812b0E1', 'Crypto' ),
			array( 'TUSD', 'ETH', '0x3886BA987236181D98F2401c507Fb8BeA7871dF2', 'Crypto' ),
			array( 'GBP', 'USD', '0x5c0Ab2d9b5a7ed9f470386e82BB36A3613cDd4b5', 'Fiat' ),
			array( 'CHF', 'USD', '0x449d117117838fFA61263B61dA6301AA2a88B13A', 'Fiat' ),
			array( 'EIGEN', 'USD', '0xf2917e602C2dCa458937fad715bb1E465305A4A1', 'Crypto' ),
			array( 'ENJ', 'ETH', '0x24D9aB51950F3d62E9144fdC2f3135DAA6Ce8D1B', 'Crypto' ),
			array( 'SUSHI', 'USD', '0xCc70F09A6CC17553b2E31954cD36E4A2d89501f7', 'Crypto' ),
			array( '1INCH', 'USD', '0xc929ad75B72593967DE83E7F7Cda0493458261D9', 'Crypto' ),
			array( 'SAND', 'USD', '0x35E3f7E558C04cE7eEE1629258EcbbA03B36Ec56', 'Crypto' ),
			array( 'ENS', 'USD', '0x5C00128d4d1c2F4f652C267d7bcdD7aC99C16E16', 'Crypto' ),
			array( 'MKR', 'ETH', '0x24551a8Fb2A7211A25a17B1481f043A8a8adC7f2', 'Crypto' ),
			array( 'RSETH', 'ETH', '0x03c68933f7a3F76875C0bc670a58e69294cDFD01', 'Crypto' ),
			array( 'DAI', 'USD', '0xAed0c38402a5d19df6E4c03F4E2DceD6e29c1ee9', 'Crypto' ),
			array( 'KNC', 'ETH', '0x656c0544eF4C98A6a98491833A89204Abb045d6b', 'Crypto' ),
			array( 'ETH', 'USD', '0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419', 'Crypto' ),
			array( 'FTM', 'ETH', '0x2DE7E4a9488488e0058B95854CC2f7955B35dC9b', 'Crypto' ),
			array( 'USDM', 'USD', '0x079674468Fee6ab45aBfE986737A440701c49BdB', 'Crypto' ),
			array( 'CNY', 'USD', '0xeF8A4aF35cd47424672E3C590aBD37FBB7A7759a', 'Fiat' ),
			array( 'BAL', 'ETH', '0xC1438AA3823A6Ba0C159CfA8D98dF5A994bA120b', 'Crypto' ),
			array( 'SNX', 'ETH', '0x79291A9d692Df95334B1a0B3B4AE6bC606782f8c', 'Crypto' ),
			array( 'DAI', 'ETH', '0x773616E4d11A78F511299002da57A0a94577F1f4', 'Crypto' ),
			array( 'APE', 'USD', '0xD10aBbC76679a20055E167BB80A24ac851b37056', 'Crypto' ),
			array( 'FRAX', 'USD', '0xB9E1E3A9feFf48998E45Fa90847ed4D467E8BcfD', 'Crypto' ),
			array( 'HIGH', 'USD', '0x5C8D8AaB4ffa4652753Df94f299330Bb4479bF85', 'Crypto' ),
			array( 'YFI', 'ETH', '0x7c5d4F8345e66f68099581Db340cd65B078C41f4', 'Crypto' ),
			array( 'MANA', 'ETH', '0x82A44D92D6c329826dc557c5E1Be6ebeC5D5FeB9', 'Crypto' ),
			array( 'RDNT', 'USD', '0x393CC05baD439c9B36489384F11487d9C8410471', 'Crypto' ),
			array( 'USD0', 'USD', '0x7e891DEbD8FA0A4Cf6BE58Ddff5a8ca174FebDCB', 'Crypto' ),
			array( 'RPL', 'USD', '0x4E155eD98aFE9034b7A5962f6C84c86d869daA9d', 'Crypto' ),
			array( 'GRT', 'USD', '0x86cF33a451dE9dc61a2862FD94FF4ad4Bd65A5d2', 'Crypto' ),
			array( 'EUR', 'USD', '0xb49f677943BC038e9857d61E7d053CaA2C1734C1', 'Fiat' ),
			array( 'LINK', 'USD', '0x2c1d072e956AFFC0D435Cb7AC38EF18d24d9127c', 'Crypto' ),
			array( 'MLN', 'ETH', '0xDaeA8386611A157B08829ED4997A8A62B557014C', 'Crypto' ),
			array( 'SPELL', 'USD', '0x8c110B94C5f1d347fAcF5E1E938AB2db60E3c9a8', 'Crypto' ),
			array( 'FTT', 'ETH', '0xF0985f7E2CaBFf22CecC5a71282a89582c382EFE', 'Crypto' ),
			array( 'BADGER', 'ETH', '0x58921Ac140522867bf50b9E009599Da0CA4A2379', 'Crypto' ),
			array( 'JPY', 'USD', '0xBcE206caE7f0ec07b545EddE332A47C2F75bbeb3', 'Fiat' ),
			array( 'CVX', 'ETH', '0xC9CbF687f43176B302F03f5e58470b77D07c61c6', 'Crypto' ),
			array( 'BNB', 'USD', '0x14e613AC84a31f709eadbdF89C6CC390fDc9540A', 'Crypto' ),
			array( 'TRY', 'USD', '0xB09fC5fD3f11Cf9eb5E1C5Dba43114e3C9f477b5', 'Fiat' ),
			array( 'MATIC', 'USD', '0x7bAC85A8a13A4BcD8abb3eB7d6b4d632c5a57676', 'Crypto' ),
			array( 'CVX', 'USD', '0xd962fC30A72A84cE50161031391756Bf2876Af5D', 'Crypto' ),
			array( 'STETH', 'ETH', '0x86392dC19c0b719886221c78AB11eb8Cf5c52812', 'Crypto' ),
			array( 'CAD', 'USD', '0xa34317DB73e77d453b1B8d04550c44D10e981C8e', 'Fiat' ),
			array( 'STG', 'USD', '0x7A9f34a0Aa917D438e9b6E630067062B7F8f6f3d', 'Crypto' ),
			array( 'REN', 'ETH', '0x3147D7203354Dc06D9fd350c7a2437bcA92387a4', 'Crypto' ),
			array( 'SOL', 'USD', '0x4ffC43a60e009B551865A93d232E33Fce9f01507', 'Crypto' ),
			array( 'BTC', 'ETH', '0xdeb288F737066589598e9214E782fa5A8eD689e8', 'Crypto' ),
			array( 'CRV', 'ETH', '0x8a12Be339B0cD1829b91Adc01977caa5E9ac121e', 'Crypto' ),
			array( 'USDP', 'USD', '0x09023c0DA49Aaf8fc3fA3ADF34C6A7016D38D5e3', 'Crypto' ),
			array( 'NZD', 'USD', '0x3977CFc9e4f29C184D4675f4EB8e0013236e5f3e', 'Fiat' ),
			array( 'FXS', 'USD', '0x6Ebc52C8C1089be9eB3945C4350B68B8E4C2233f', 'Crypto' ),
			array( 'IMX', 'USD', '0xBAEbEFc1D023c0feCcc047Bff42E75F15Ff213E6', 'Crypto' ),
			array( 'FRAX', 'ETH', '0x14d04Fff8D21bd62987a5cE9ce543d2F1edF5D3E', 'Crypto' ),
			array( 'SNX', 'USD', '0xDC3EA94CD0AC27d9A86C180091e7f78C683d3699', 'Crypto' ),
			array( 'RETH', 'ETH', '0x536218f9E9Eb48863970252233c8F271f554C2d0', 'Crypto' ),
			array( 'USDC', 'USD', '0x8fFfFfd4AfB6115b954Bd326cbe7B4BA576818f6', 'Crypto' ),
			array( 'APE', 'ETH', '0xc7de7f4d4C9c991fF62a07D18b3E31e349833A18', 'Crypto' ),
			array( 'SHIB', 'ETH', '0x8dD1CD88F43aF196ae478e91b9F5E4Ac69A97C61', 'Crypto' ),
			array( 'AMPL', 'USD', '0xe20CA8D7546932360e37E9D72c1a47334af57706', 'Crypto' ),
			array( 'AAVE', 'USD', '0x547a514d5e3769680Ce22B2361c10Ea13619e8a9', 'Crypto' ),
			array( 'CRV', 'USD', '0xCd627aA160A6fA45Eb793D19Ef54f5062F20f33f', 'Crypto' ),
			array( 'UNI', 'USD', '0x553303d460EE0afB37EdFf9bE42922D8FF63220e', 'Crypto' ),
			array( 'ZRX', 'USD', '0x2885d15b8Af22648b98B122b22FDF4D2a56c6023', 'Crypto' ),
			array( 'MKR', 'USD', '0xec1D1B3b0443256cc3860e24a46F108e699484Aa', 'Crypto' ),
			array( 'ARB', 'USD', '0x31697852a68433DbCc2Ff612c516d69E3D9bd08F', 'Crypto' ),
			array( 'LDO', 'ETH', '0x4e844125952D32AcdF339BE976c98E22F6F318dB', 'Crypto' ),
			array( 'SGD', 'USD', '0xe25277fF4bbF9081C75Ab0EB13B4A13a721f3E13', 'Fiat' ),
			array( 'TUSD', 'USD', '0xec746eCF986E2927Abd291a2A1716c940100f8Ba', 'Crypto' ),
		);
	}

	/** @inheritdoc */
	public function getAddress( SymbolPair $symbol_pair ): ?string {
		$data  = $this->data();
		$base  = $symbol_pair->base();
		$quote = $symbol_pair->quote();

		$filtered = array_filter( $data, fn( $d ) => $d[ self::BASE_SYMBOL_INDEX ] === $base && $d[ self::QUOTE_SYMBOL_INDEX ] === $quote );

		return count( $filtered ) === 1 ? array_values( $filtered )[0][ self::ADDRESS_INDEX ] : null;
	}


	public function getAssetClass( SymbolPair $symbol_pair ): ?string {
		$data  = $this->data();
		$base  = $symbol_pair->base();
		$quote = $symbol_pair->quote();

		$filtered = array_filter( $data, fn( $d ) => $d[ self::BASE_SYMBOL_INDEX ] === $base && $d[ self::QUOTE_SYMBOL_INDEX ] === $quote );

		return count( $filtered ) === 1 ? array_values( $filtered )[0][ self::BASE_SYMBOL_CATEGORY_INDEX ] : null;
	}

	/** @inheritdoc */
	public function fiatSymbols(): array {
		$filtered = array_filter( $this->data(), fn( $d ) => $d[ self::BASE_SYMBOL_CATEGORY_INDEX ] === 'Fiat' );
		return array_values( array_map( fn( $d ) => $d[ self::BASE_SYMBOL_INDEX ], $filtered ) );
	}
}
