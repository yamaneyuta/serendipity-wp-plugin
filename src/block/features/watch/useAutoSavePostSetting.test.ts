import { useAutoSavePostSetting } from './useAutoSavePostSetting';
import { useEditorProperty } from '../../provider/windowData/editor/useEditorProperty';
import { useIsDataChanged } from './useIsDataChanged';
import { useSavePostSettingCallback } from '../../provider/serverData/postSetting/useSavePostSettingCallback';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useInputPriceValue } from '../../provider/userInput/inputPriceValue/useInputPriceValue';
import { useSelectedPriceSymbol } from '../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol';
import { NetworkType } from '../../../types/gql/generated';
import { renderHook } from '../../../../jest-lib/renderHook';

jest.mock( '../../provider/windowData/editor/useEditorProperty' );
jest.mock( './useIsDataChanged' );
jest.mock( '../../provider/serverData/postSetting/useSavePostSettingCallback' );
jest.mock( '../../provider/userInput/selectedNetwork/useSelectedNetwork' );
jest.mock( '../../provider/userInput/inputPriceValue/useInputPriceValue' );
jest.mock( '../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol' );

/**
 * ユーザーが入力した値に問題が無い場合、タイミングによってsaveが呼ばれるかどうかのテスト
 */
describe( '[5C104F1C] useAutoSavePostSetting - valid postSettingInput', () => {
	// 手動保存中(isSaving: true && isAutosavingPost: false)かつデータが変更されている場合にsaveが呼ばれる
	// isSaving, isAutosavingPost, isDataChanged, saveCalledCount
	const dataset: [ boolean, boolean, boolean, number ][] = [
		// true: 0
		[ false, false, false, 0 ],
		// true: 1
		[ true, false, false, 0 ],
		[ false, true, false, 0 ],
		[ false, false, true, 0 ],
		// true: 2
		[ true, true, false, 0 ],
		[ true, false, true, 1 ], // ○ 手動保存中でデータが変更されている場合、保存が呼ばれる
		[ false, true, true, 0 ],
		// true: 3
		[ true, true, true, 0 ],
	];

	for ( const [ isSaving, isAutosavingPost, isDataChanged, saveCalledCount ] of dataset ) {
		it( `[5A9884D1] should call save when isSaving: ${ isSaving }, isAutosavingPost: ${ isAutosavingPost }, isDataChanged: ${ isDataChanged }`, () => {
			// ARRANGE
			const save = jest.fn();
			( useEditorProperty as jest.Mock ).mockReturnValue( { isSaving, isAutosavingPost } );
			( useIsDataChanged as jest.Mock ).mockReturnValue( isDataChanged );
			( useSavePostSettingCallback as jest.Mock ).mockReturnValue( save );
			( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork: NetworkType.Mainnet } );
			( useInputPriceValue as jest.Mock ).mockReturnValue( { inputPriceValue: '100.00' } );
			( useSelectedPriceSymbol as jest.Mock ).mockReturnValue( { selectedPriceSymbol: 'USD' } );

			// ACT
			renderHook( () => useAutoSavePostSetting() );

			// ASSERT
			// saveが呼ばれたかどうかを確認
			expect( save ).toHaveBeenCalledTimes( saveCalledCount );
		} );
	}
} );

/**
 * ユーザーが入力した値に問題がある場合のテスト
 */
describe( '[F5425714] useAutoSavePostSetting - valid postSettingInput', () => {
	// nullやundefinedが含まれる場合、saveは呼ばれない
	const dataset: [ NetworkType | null | undefined, string | null | undefined, string | null | undefined ][] = [
		[ null, '100.00', 'USD' ],
		[ undefined, '100.00', 'USD' ],
		[ NetworkType.Mainnet, null, 'USD' ],
		[ NetworkType.Mainnet, undefined, 'USD' ],
		[ NetworkType.Mainnet, '100.00', null ],
		[ NetworkType.Mainnet, '100.00', undefined ],
	];

	for ( const [ selectedNetwork, inputPriceValue, selectedPriceSymbol ] of dataset ) {
		it( `[7C5E549E] should not call save when selectedNetwork: ${ selectedNetwork }, inputPriceValue: ${ inputPriceValue }, selectedPriceSymbol: ${ selectedPriceSymbol }`, () => {
			// ARRANGE
			const save = jest.fn();
			( useEditorProperty as jest.Mock ).mockReturnValue( { isSaving: true, isAutosavingPost: false } );
			( useIsDataChanged as jest.Mock ).mockReturnValue( true );
			( useSavePostSettingCallback as jest.Mock ).mockReturnValue( save );
			( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork } );
			( useInputPriceValue as jest.Mock ).mockReturnValue( { inputPriceValue } );
			( useSelectedPriceSymbol as jest.Mock ).mockReturnValue( { selectedPriceSymbol } );

			// ACT, ASSERT
			expect( () => renderHook( () => useAutoSavePostSetting() ) ).toThrow( '[136AA840]' );
			expect( save ).not.toHaveBeenCalled(); // saveは呼ばれていないこと
		} );
	}
} );
