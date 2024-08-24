import { useIsSellingPriceSymbolChanged } from './useIsSellingPriceSymbolChanged';
import { useSellingPriceSymbol } from '../../../provider/serverData/useSellingPriceSymbol';
import { useSelectedPriceSymbol } from '../../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol';

jest.mock( '../../../provider/serverData/useSellingPriceSymbol' );
jest.mock( '../../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol' );

describe( '[EE3ACF32] useIsSellingPriceSymbolChanged()', () => {
	// undefinedが含まれる場合は変更されたと見なさない
	// [サーバーから取得した販売価格の通貨シンボル, ユーザーが選択した販売価格の通貨シンボル, 期待値(変更されたかどうか)]
	const dataset: [ string | null | undefined, string | null | undefined, boolean ][] = [
		[ 'USD', 'USD', false ],
		[ 'USD', 'EUR', true ],
		[ 'USD', null, true ],
		[ 'USD', undefined, false ],
		[ 'EUR', 'USD', true ],
		[ 'EUR', 'EUR', false ],
		[ 'EUR', null, true ],
		[ 'EUR', undefined, false ],
		[ null, 'USD', true ], // 通常発生しない
		[ null, 'EUR', true ], // 通常発生しない
		[ null, null, false ],
		[ null, undefined, false ],
		[ undefined, 'USD', false ],
		[ undefined, 'EUR', false ],
		[ undefined, null, false ], // 通常発生しない
		[ undefined, undefined, false ],
	];

	for ( const [ srv, usr, expected ] of dataset ) {
		it( `[2CC976C0] useIsSellingPriceSymbolChanged() - srv: (${ srv }, usr: ${ usr }) -> ${ expected }`, async () => {
			( useSellingPriceSymbol as jest.Mock ).mockReturnValue( srv );
			( useSelectedPriceSymbol as jest.Mock ).mockReturnValue( { selectedPriceSymbol: usr } );

			const isSellingPriceSymbolChanged = useIsSellingPriceSymbolChanged();
			expect( isSellingPriceSymbolChanged ).toEqual( expected );
		} );
	}
} );
