import { NetworkType } from '../../../types/gql/generated';
import { useGetSellableSymbolsCallback as sut } from './useGetSellableSymbolsCallback';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';
import { renderHook } from '../../../../jest-lib/renderHook';

jest.mock( '../../provider/postSetting/usePostSetting' );

type UsePostSettingResult = ReturnType< typeof usePostSetting >;

/*
 * 基本的なテストは./useSelectableSymbols.test.tsxで実施。
 * ここでは、カバーしていないケースを中心にテストする。
 */

/**
 * 無効なネットワーク種別が指定された場合のテスト。
 */
it( '[8F11FAF5] useGetSellableSymbolsCallback() - invalid network', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT, ASSERT
	const getSellableSymbols = renderHook( () => sut() ).result.current;
	expect( () => getSellableSymbols( 'INVALID_NETWORK' as unknown as NetworkType ) ).toThrow( '[3D102039]' );
} );

/**
 * APIの戻り値が不正な場合のテスト。
 */
it( '[E0E21F73] useGetSellableSymbolsCallback() - invalid response', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: undefined, // 不正な値(APIの仕様上、undefinedになることはない)
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT, ASSERT
	const getMainnet = renderHook( () => sut() ).result.current;
	expect( () => getMainnet( NetworkType.Mainnet ) ).toThrow( '[519DA805]' );
} );
