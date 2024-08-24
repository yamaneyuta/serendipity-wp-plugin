import { useNotifyDataChangedToEditor } from './useNotifyDataChangedToEditor';
import { useIsDataChanged } from './useIsDataChanged';
import { renderHook } from '../../../../jest-lib/renderHook';

jest.mock( './useIsDataChanged' );

it.each`
	isDataChanged | calledCount
	${ false }    | ${ 0 }
	${ true }     | ${ 1 }
`(
	'[80620CE6] useNotifyDataChangedToEditor() - isDataChanged: $isDataChanged -> calledCount: $calledCount',
	( { isDataChanged, calledCount } ) => {
		// ARRANGE
		const onDataChangedCallback = jest.fn();
		( useIsDataChanged as jest.Mock ).mockReturnValue( isDataChanged );

		// ACT
		renderHook( () => useNotifyDataChangedToEditor( onDataChangedCallback ) );

		// ASSERT
		// onDataChangedCallbackが呼ばれた回数を確認
		expect( onDataChangedCallback ).toHaveBeenCalledTimes( calledCount );
	}
);
