import { useSelectableSymbols as sut } from './useSelectableSymbols';
import { usePostSetting } from '../../provider/serverData/postSetting/usePostSetting';
import { renderHook } from '../../../../jest-lib/renderHook';
import { useSelectedNetworkCategory } from '../../provider/widgetState/selectedNetworkCategory/useSelectedNetworkCategory';
import { NetworkCategory } from '../../../types/NetworkCategory';

jest.mock( '../../provider/serverData/postSetting/usePostSetting' );
jest.mock( '../../provider/widgetState/selectedNetworkCategory/useSelectedNetworkCategory' );

type UsePostSettingResult = ReturnType< typeof usePostSetting >;

/**
 * 通常のテスト。メインネットが選択された時に対応する通貨シンボルが取得できる。
 */
it( '[23C5844D] useSelectableSymbols() - default(mainnet)', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		networkCategories: [
			{ id: 1, sellableSymbols: [ 'JPY' ] },
			{ id: 2, sellableSymbols: [ 'USD' ] },
			{ id: 3, sellableSymbols: [ 'EUR', 'GBP' ] },
		],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetworkCategory as jest.Mock ).mockReturnValue( {
		selectedNetworkCategory: NetworkCategory.mainnet(),
	} );

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
		networkCategories: [
			{ id: 1, sellableSymbols: [ 'JPY' ] },
			{ id: 2, sellableSymbols: [ 'USD' ] },
			{ id: 3, sellableSymbols: [ 'EUR', 'GBP' ] },
		],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetworkCategory as jest.Mock ).mockReturnValue( {
		selectedNetworkCategory: NetworkCategory.testnet(),
	} );

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
		networkCategories: [
			{ id: 1, sellableSymbols: [ 'JPY' ] },
			{ id: 2, sellableSymbols: [ 'USD' ] },
			{ id: 3, sellableSymbols: [ 'EUR', 'GBP' ] },
		],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetworkCategory as jest.Mock ).mockReturnValue( {
		selectedNetworkCategory: NetworkCategory.privatenet(),
	} );

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
		networkCategories: [
			{ id: 1, sellableSymbols: [ 'JPY' ] },
			{ id: 2, sellableSymbols: [ 'USD' ] },
			{ id: 3, sellableSymbols: [ 'EUR', 'GBP' ] },
		],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetworkCategory as jest.Mock ).mockReturnValue( { selectedNetworkCategory: null } );

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
	( useSelectedNetworkCategory as jest.Mock ).mockReturnValue( {
		selectedNetworkCategory: NetworkCategory.mainnet(),
	} );

	// ACT
	const sellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	// 通常、このような状態は発生しないが、データ取得中のためundefinedが返ることを確認
	expect( sellableSymbols ).toBeUndefined();
} );

/**
 * 選択可能な通貨シンボルが取得できなかった場合のテスト。
 */
it( '[E9CD00AF] useSelectableSymbols() - []', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		networkCategories: [
			{ id: 1, sellableSymbols: [] },
			{ id: 2, sellableSymbols: [] },
			{ id: 3, sellableSymbols: [] },
		],
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	( useSelectedNetworkCategory as jest.Mock ).mockReturnValue( {
		selectedNetworkCategory: NetworkCategory.mainnet(),
	} );

	// ACT
	const mainnetSellableSymbols = renderHook( () => sut() ).result.current;

	// ASSERT
	expect( mainnetSellableSymbols ).toEqual( [] );
} );

// /**
//  * 無効なネットワーク種別が指定された場合のテスト。
//  */
// it( '[BF4948EE] useSelectableSymbols() - invalid network type', async () => {
// 	// ARRANGE
// 	const res: UsePostSettingResult = {
// 		mainnetSellableSymbols: [ 'JPY' ],
// 		testnetSellableSymbols: [ 'USD' ],
// 		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
// 	};
// 	( usePostSetting as jest.Mock ).mockReturnValue( res );
// 	( useSelectedNetworkCategory as jest.Mock ).mockReturnValue( {
// 		selectedNetworkCategory: 'INVALID_NETWORK' as unknown as NetworkType,
// 	} );

// 	// ACT, ASSERT
// 	expect( () => renderHook( () => sut() ) ).toThrow( '[3D102039]' );
// } );
