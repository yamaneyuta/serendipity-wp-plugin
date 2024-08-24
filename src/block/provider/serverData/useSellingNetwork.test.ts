import { useSellingNetwork } from './useSellingNetwork';
import { usePostSetting } from './postSetting/usePostSetting';
import { NetworkType } from '../../../types/gql/generated';
import { renderHook } from '../../../../jest-lib/renderHook';

jest.mock( './postSetting/usePostSetting' );

describe( '[72FD7A16] useSellingNetwork', () => {
	/**
	 * サーバーから販売ネットワークが取得できた場合のテスト
	 */
	it( '[1390EDA7] should return sellingNetwork', () => {
		// ARRANGE
		// サーバーからネットワーク種別を取得
		( usePostSetting as jest.Mock ).mockReturnValue( { sellingNetwork: NetworkType.Mainnet } );

		// ACT
		const result = renderHook( () => useSellingNetwork() ).result.current;

		// ASSERT
		expect( result ).toBe( NetworkType.Mainnet );
	} );

	/**
	 * サーバーから販売ネットワークが取得できなかった場合のテスト
	 */
	it( '[2E6658D2] should return null when sellingNetwork does not exist', () => {
		// ARRANGE
		// サーバーからネットワーク種別を取得
		( usePostSetting as jest.Mock ).mockReturnValue( { sellingNetwork: null } );

		// ACT
		const result = renderHook( () => useSellingNetwork() ).result.current;

		// ASSERT
		expect( result ).toBeNull();
	} );

	/**
	 * 販売ネットワークをサーバーから取得中の場合のテスト
	 */
	it( '[9A014927] should return undefined when sellingNetwork is loading', () => {
		// ARRANGE
		// サーバーからネットワーク種別を取得
		( usePostSetting as jest.Mock ).mockReturnValue( undefined );

		// ACT
		const result = renderHook( () => useSellingNetwork() ).result.current;

		// ASSERT
		expect( result ).toBeUndefined();
	} );
} );
