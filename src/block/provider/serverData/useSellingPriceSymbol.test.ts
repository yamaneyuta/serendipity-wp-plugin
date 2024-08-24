import { useSellingPriceSymbol } from './useSellingPriceSymbol';
import { usePostSetting } from './postSetting/usePostSetting';
import { renderHook } from '../../../../jest-lib/renderHook';

jest.mock( './postSetting/usePostSetting' );

describe( '[17CB91CF] useSellingPriceSymbol', () => {
	/**
	 * サーバーから販売価格の通貨シンボルが取得できた場合のテスト
	 */
	it( '[403BE6AB] should return sellingPrice symbol', () => {
		// ARRANGE
		// サーバーから販売価格の通貨シンボルを取得
		( usePostSetting as jest.Mock ).mockReturnValue( { sellingPrice: { symbol: 'USD' } } );

		// ACT
		const result = renderHook( () => useSellingPriceSymbol() ).result.current;

		// ASSERT
		expect( result ).toBe( 'USD' );
	} );

	/**
	 * サーバーから販売価格の通貨シンボルが取得できなかった場合のテスト
	 */
	it( '[827E8E5C] should return null when sellingPrice does not exist', () => {
		// ARRANGE
		// サーバーから販売価格の通貨シンボルを取得
		( usePostSetting as jest.Mock ).mockReturnValue( { sellingPrice: null } );

		// ACT
		const result = renderHook( () => useSellingPriceSymbol() ).result.current;

		// ASSERT
		expect( result ).toBeNull();
	} );

	/**
	 * 販売価格の通貨シンボルをサーバーから取得中の場合のテスト
	 */
	it( '[0E907368] should return undefined when sellingPrice is loading', () => {
		// ARRANGE
		// サーバーから販売価格の通貨シンボルを取得
		( usePostSetting as jest.Mock ).mockReturnValue( undefined );

		// ACT
		const result = renderHook( () => useSellingPriceSymbol() ).result.current;

		// ASSERT
		expect( result ).toBeUndefined();
	} );
} );
