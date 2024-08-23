import { useIsSellingPriceValueChanged } from './useIsSellingPriceValueChanged';
import { useSellingPriceValue } from '../../../provider/serverData/useSellingPriceValue';
import { useInputPriceValue } from '../../../provider/userInput/inputPriceValue/useInputPriceValue';

jest.mock( '../../../provider/serverData/useSellingPriceValue' );
jest.mock( '../../../provider/userInput/inputPriceValue/useInputPriceValue' );

type HexType = string | null | undefined;
type DecimalsType = number | null | undefined;

describe( '[482CA42A] useIsSellingPriceValueChanged()', () => {
	// undefinedが含まれる場合は変更されたと見なさない
	// [サーバーから取得した販売価格の数量, サーバーから取得した販売価格の小数点以下桁数, ユーザーが入力した販売価格の数量, ユーザーが入力した販売価格の小数点以下桁数, 期待値(変更されたかどうか)]
	const dataset: [ HexType, DecimalsType, HexType, DecimalsType, boolean ][] = [
		[ '0x1234', 2, '0x1234', 2, false ], // 同じ値
		[ '0x1234', 2, '0x5678', 2, true ], // 数量が異なる
		[ '0x1234', 2, '0x1234', 3, true ], // 小数点以下桁数が異なる
		// [ '0x1234', 2, null, 2, true ],
		// [ '0x1234', 2, undefined, 2, false ],
		// [ '0x1234', 2, '0x1234', null, true ],
		// [ '0x1234', 2, '0x1234', undefined, false ],
		[ '0x1234', 2, null, null, true ],
		// [ '0x1234', 2, null, undefined, false ],
		// [ '0x1234', 2, undefined, null, false ],
		[ '0x1234', 2, undefined, undefined, false ],
		// [ null, 2, '0x1234', 2, true ],
		// [ undefined, 2, '0x1234', 2, false ],
		[ null, null, '0x1234', 2, true ], // 通常発生しない
		[ undefined, undefined, '0x1234', 2, false ], // 通常発生しない
		// [ null, null, null, 2, false ],
		// [ undefined, undefined, undefined, 2, false ],
		// [ null, null, '0x1234', null, true ],
		// [ undefined, undefined, '0x1234', undefined, false ],
		[ null, null, null, null, false ],
		[ undefined, undefined, undefined, undefined, false ],
	];

	for ( const [ srvAmountHex, srvDecimals, inputAmountHex, inputDecimals, expected ] of dataset ) {
		it( `[3BFEBE12] useIsSellingPriceValueChanged() - srv: (${ srvAmountHex }, ${ srvDecimals }), usr: (${ inputAmountHex }, ${ inputDecimals }) -> ${ expected }`, async () => {
			( useSellingPriceValue as jest.Mock ).mockReturnValue( { amountHex: srvAmountHex, decimals: srvDecimals } );
			( useInputPriceValue as jest.Mock ).mockReturnValue( { inputAmountHex, inputDecimals } );

			const isSellingPriceValueChanged = useIsSellingPriceValueChanged();
			expect( isSellingPriceValueChanged ).toEqual( expected );
		} );
	}
} );
