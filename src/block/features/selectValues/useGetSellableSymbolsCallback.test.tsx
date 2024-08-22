import { NetworkType } from '../../../types/gql/generated';
import { useGetSellableSymbolsCallback as sut } from './useGetSellableSymbolsCallback';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';
import { renderHook } from '@testing-library/react';

jest.mock( '../../provider/postSetting/usePostSetting' );

type UsePostSettingResult = ReturnType< typeof usePostSetting >;

/**
 * 通常のテスト。各ネットワークで販売可能な通貨シンボルを取得できる場合。
 */
it( 'useGetSellableSymbolsCallback() - default', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	const { result: getMainnet } = renderHook( () => sut( NetworkType.Mainnet ) );
	const { result: getTestnet } = renderHook( () => sut( NetworkType.Testnet ) );
	const { result: getPrivatenet } = renderHook( () => sut( NetworkType.Privatenet ) );

	// ACT
	const mainnetSellableSymbols = getMainnet.current();
	const testnetSellableSymbols = getTestnet.current();
	const privatenetSellableSymbols = getPrivatenet.current();

	// ASSERT
	expect( mainnetSellableSymbols ).toEqual( [ 'JPY' ] );
	expect( testnetSellableSymbols ).toEqual( [ 'USD' ] );
	expect( privatenetSellableSymbols ).toEqual( [ 'EUR', 'GBP' ] );
} );

/**
 * 投稿設定をサーバーから取得している最中のテスト。
 */
it( 'useGetSellableSymbolsCallback() - loading', async () => {
	// ARRANGE
	const res: UsePostSettingResult = undefined;
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	const { result: getMainnet } = renderHook( () => sut( NetworkType.Mainnet ) );
	const { result: getTestnet } = renderHook( () => sut( NetworkType.Testnet ) );
	const { result: getPrivatenet } = renderHook( () => sut( NetworkType.Privatenet ) );

	// ACT
	const mainnetSellableSymbols = getMainnet.current();
	const testnetSellableSymbols = getTestnet.current();
	const privatenetSellableSymbols = getPrivatenet.current();

	// ASSERT
	expect( mainnetSellableSymbols ).toBeUndefined();
	expect( testnetSellableSymbols ).toBeUndefined();
	expect( privatenetSellableSymbols ).toBeUndefined();
} );

/**
 * 販売可能な通貨シンボルが取得できなかった場合のテスト。
 */
it( 'useGetSellableSymbolsCallback() - null', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: null,
		testnetSellableSymbols: null,
		privatenetSellableSymbols: null,
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	const { result: getMainnet } = renderHook( () => sut( NetworkType.Mainnet ) );
	const { result: getTestnet } = renderHook( () => sut( NetworkType.Testnet ) );
	const { result: getPrivatenet } = renderHook( () => sut( NetworkType.Privatenet ) );

	// ACT
	const mainnetSellableSymbols = getMainnet.current();
	const testnetSellableSymbols = getTestnet.current();
	const privatenetSellableSymbols = getPrivatenet.current();

	// ASSERT
	expect( mainnetSellableSymbols ).toBeNull();
	expect( testnetSellableSymbols ).toBeNull();
	expect( privatenetSellableSymbols ).toBeNull();
} );

/**
 * 無効なネットワーク種別が指定された場合のテスト。
 */
it( 'useGetSellableSymbolsCallback() - null', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );
	const { result: getMainnet } = renderHook( () => sut( 'INVALID_NETWORK' as unknown as NetworkType ) );

	// ACT
	try {
		getMainnet.current();
		expect( true ).toBeFalsy(); // ここには到達しない
	} catch ( e ) {
		// ASSERT
		expect( e instanceof Error ).toBeTruthy();
	}
} );
