import { useIsSellingPriceValueChanged } from './useIsSellingPriceValueChanged';
import { useSellingPriceValue } from '../../../provider/serverData/useSellingPriceValue';
import { useInputPriceValue } from '../../../provider/widgetState/inputPriceValue/useInputPriceValue';

jest.mock( '../../../provider/serverData/useSellingPriceValue' );
jest.mock( '../../../provider/widgetState/inputPriceValue/useInputPriceValue' );

describe( '[482CA42A] useIsSellingPriceValueChanged()', () => {
	// undefinedが含まれる場合は変更されたと見なさない
	// [サーバーから取得した販売価格の値, ユーザーが入力した販売価格の値, 期待値(変更されたかどうか)]
	const dataset: [ string | null | undefined, string | null | undefined, boolean ][] = [
		[ '1234', '1234', false ], // 同じ値
		[ '1234', '5678', true ], // 値が異なる
		[ '1234', undefined, false ],
		[ '1234', null, true ],
		[ undefined, '1234', false ], // 通常発生しない
		[ null, '1234', true ], // 通常発生しない
		[ undefined, undefined, false ], // どちらも取得していない
	];

	for ( const [ sellingPriceValue, inputPriceValue, expected ] of dataset ) {
		it( `[3BFEBE12] useIsSellingPriceValueChanged() - sellingPriceValue: (${ sellingPriceValue }, inputPriceValue: (${ inputPriceValue }, -> ${ expected }`, async () => {
			( useSellingPriceValue as jest.Mock ).mockReturnValue( sellingPriceValue );
			( useInputPriceValue as jest.Mock ).mockReturnValue( { inputPriceValue } );

			const isSellingPriceValueChanged = useIsSellingPriceValueChanged();
			expect( isSellingPriceValueChanged ).toEqual( expected );
		} );
	}
} );
