import { useNotifyDataChangedToEditor } from './useNotifyDataChangedToEditor';
import { useIsDataChanged } from './useIsDataChanged';
import { renderHook } from '../../../../jest-lib/renderHook';

jest.mock( './useIsDataChanged' );

describe( '[AD82F369] useNotifyDataChangedToEditor()', () => {
	// データが変更されている場合にonDataChangedCallbackが呼ばれる
	// isDataChanged, calledCount
	const dataset: [ boolean, number ][] = [
		[ false, 0 ],
		[ true, 1 ],
	];

	for ( const [ isDataChanged, calledCount ] of dataset ) {
		it( `[80620CE6] useNotifyDataChangedToEditor() - isDataChanged: ${ isDataChanged } -> calledCount: ${ calledCount }`, () => {
			// ARRANGE
			const onDataChangedCallback = jest.fn();
			( useIsDataChanged as jest.Mock ).mockReturnValue( isDataChanged );

			// ACT
			renderHook( () => useNotifyDataChangedToEditor( onDataChangedCallback ) );

			// ASSERT
			// onDataChangedCallbackが呼ばれた回数を確認
			expect( onDataChangedCallback ).toHaveBeenCalledTimes( calledCount );
		} );
	}
} );
