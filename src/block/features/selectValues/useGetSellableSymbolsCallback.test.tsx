import assert from 'node:assert/strict';
import { NetworkType } from '../../../types/gql/generated';
import { useGetSellableSymbolsCallback as sut } from './useGetSellableSymbolsCallback';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';
import { renderHook } from '@testing-library/react';

jest.mock( '../../provider/postSetting/usePostSetting' );

type UsePostSettingResult = ReturnType< typeof usePostSetting >;

/*
 * 基本的なテストは./useSelectableSymbols.test.tsxで実施。
 * ここでは、カバーしていないケースを中心にテストする。
 */

/**
 * 無効なネットワーク種別が指定された場合のテスト。
 */
it( 'useGetSellableSymbolsCallback() - invalid network', async () => {
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
		assert( e instanceof Error );
		expect( e.message ).toContain( '[3D102039]' );
	}
} );

/**
 * APIの戻り値が不正な場合のテスト。
 */
it( 'useGetSellableSymbolsCallback() - invalid response', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: undefined, // 不正な値(APIの仕様上、undefinedになることはない)
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	const { result: getMainnet } = renderHook( () => sut( NetworkType.Mainnet ) );

	try {
		// ACT
		getMainnet.current();
		expect( true ).toBeFalsy(); // ここには到達しない
	} catch ( e ) {
		// ASSERT
		assert( e instanceof Error );
		expect( e.message ).toContain( '[519DA805]' );
	}
} );
