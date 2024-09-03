import { useIsDataChanged } from './useIsDataChanged';
import { useIsSellingPriceSymbolChanged } from './dataChanged/useIsSellingPriceSymbolChanged';

jest.mock( './dataChanged/useIsSellingPriceSymbolChanged' );

/**
 * 画面上の情報とサーバーに保存されているデータに違いがあるかどうかを判定するテスト
 */
describe( '[83691B9A] useIsDataChanged', () => {
	const dataset: [ boolean, boolean, boolean, boolean ][] = [
		[ false, false, false, false ], // いずれも変更されていない
		[ true, true, true, true ], // すべて変更されている
		// いずれかが変更されている
		[ true, false, false, true ],
		[ false, true, false, true ],
		[ false, false, true, true ],
		// [true, true, false, true],
		// [true, false, true, true],
		// [false, true, true, true],
	];

	for ( const [ networkChanged, priceValueChanged, priceSymbolChanged, expected ] of dataset ) {
		it( `[A42C8A21] should return ${ expected } when networkChanged: ${ networkChanged }, priceValueChanged: ${ priceValueChanged }, priceSymbolChanged: ${ priceSymbolChanged }`, () => {
			( useIsSellingPriceSymbolChanged as jest.Mock ).mockReturnValue( priceSymbolChanged );

			const result = useIsDataChanged();
			expect( result ).toBe( expected );
		} );
	}
} );
