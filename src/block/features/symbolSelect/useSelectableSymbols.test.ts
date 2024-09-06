import { NetworkType } from '../../../types/gql/generated';
import { useSelectableSymbols as sut } from './useSelectableSymbols';
import { usePostSetting } from '../../provider/serverData/postSetting/usePostSetting';
import { renderHook } from '../../../../jest-lib/renderHook';
import { useSelectedNetwork } from '../../provider/widgetState/selectedNetwork/useSelectedNetwork';

jest.mock( '../../provider/serverData/postSetting/usePostSetting' );
jest.mock( '../../provider/widgetState/selectedNetwork/useSelectedNetwork' );

type UsePostSettingResult = ReturnType< typeof usePostSetting >;

/**
 * 通常のテスト。メインネットが選択された時に対応する通貨シンボルが取得できる。
 */
it( '[23C5844D] useSelectableSymbols() - default(mainnet)', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork: NetworkType.Mainnet } );

	// ACT
	const mainnetSellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	expect( mainnetSellableSymbols ).toEqual( [ 'JPY' ] );
} );

/**
 * 通常のテスト。テストネットが選択された時に対応する通貨シンボルが取得できる。
 */
it( '[69581160] useSelectableSymbols() - default(testnet)', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork: NetworkType.Testnet } );

	// ACT
	const mainnetSellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	expect( mainnetSellableSymbols ).toEqual( [ 'USD' ] );
} );

/**
 * 通常のテスト。テストネットが選択された時に対応する通貨シンボルが取得できる。
 */
it( '[B43240DF] useSelectableSymbols() - default(privatenet)', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork: NetworkType.Privatenet } );

	// ACT
	const mainnetSellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	expect( mainnetSellableSymbols ).toEqual( [ 'EUR', 'GBP' ] );
} );

/**
 * 投稿設定をサーバーから取得している最中のテスト。
 */
it( '[1DDC9FA6] useSelectableSymbols(undefined) - loading', async () => {
	// ARRANGE
	const res: UsePostSettingResult = undefined;
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT
	const sellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	expect( sellableSymbols ).toBeUndefined();
} );

/**
 * 販売ネットワークが未指定の場合のテスト
 */
it( '[1DDC9FA6] useSelectableSymbols(null) - loading', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork: null } );

	// ACT
	const sellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	expect( sellableSymbols ).toBeNull();
} );

/**
 * 投稿設定をサーバーから取得している最中に不正なネットワーク種別が指定された場合のテスト。
 */
it( '[1DDC9FA6] useSelectableSymbols() - loading, invalid network type', async () => {
	// ARRANGE
	const res: UsePostSettingResult = undefined;
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork: NetworkType.Mainnet } );

	// ACT
	const sellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	// 通常、このような状態は発生しないが、データ取得中のためundefinedが返ることを確認
	expect( sellableSymbols ).toBeUndefined();
} );

/**
 * 選択可能な通貨シンボルが取得できなかった場合のテスト。
 */
it( '[E9CD00AF] useSelectableSymbols() - null', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: null,
		testnetSellableSymbols: null,
		privatenetSellableSymbols: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork: NetworkType.Mainnet } );

	// ACT
	const mainnetSellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	expect( mainnetSellableSymbols ).toBeNull();
} );

/**
 * 無効なネットワーク種別が指定された場合のテスト。
 */
it( '[BF4948EE] useSelectableSymbols() - invalid network type', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetwork as jest.Mock ).mockReturnValue( {
		selectedNetwork: 'INVALID_NETWORK' as unknown as NetworkType,
	} );

	// ACT, ASSERT
	expect( () => renderHook( () => sut() ) ).toThrow( '[3D102039]' );
} );
