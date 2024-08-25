import { useSellingPriceValue } from './useSellingPriceValue';
import { usePostSetting } from './postSetting/usePostSetting';
import { renderHook } from '../../../../jest-lib/renderHook';

jest.mock( './postSetting/usePostSetting' );

describe( '[17CB91CF] useSellingPriceValue', () => {
	/**
	 * サーバーから販売価格が取得できた場合のテスト
	 */
	it( '[0E907368] should return formatted sellingPrice value', () => {
		// ARRANGE
		const mockSellingPrice = { amountHex: '0x04d2', decimals: 2 };
		( usePostSetting as jest.Mock ).mockReturnValue( { sellingPrice: mockSellingPrice } );

		// ACT
		const result = renderHook( () => useSellingPriceValue() ).result.current;

		// ASSERT
		expect( result ).toBe( '12.34' );
	} );

	/**
	 * サーバーから販売価格を取得中の場合のテスト
	 */
	it( '[403BE6AB] should return undefined when sellingPrice is loading', () => {
		// ARRANGE
		( usePostSetting as jest.Mock ).mockReturnValue( undefined );

		// ACT
		const result = renderHook( () => useSellingPriceValue() ).result.current;

		// ASSERT
		expect( result ).toBeUndefined();
	} );

	/**
	 * サーバーから販売価格がnullの場合のテスト
	 */
	it( '[827E8E5C] should return null when sellingPrice does not exist', () => {
		// ARRANGE
		( usePostSetting as jest.Mock ).mockReturnValue( { sellingPrice: null } );

		// ACT
		const result = renderHook( () => useSellingPriceValue() ).result.current;

		// ASSERT
		expect( result ).toBeNull();
	} );
} );
