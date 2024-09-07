import { useEffect } from 'react';
import { amountToInputValue } from '@yamaneyuta/serendipity-lib-js-price-format';
import { useSelectedNetworkCategory } from '../../provider/widgetState/selectedNetworkCategory/useSelectedNetworkCategory';
import { useInputPriceValue } from '../../provider/widgetState/inputPriceValue/useInputPriceValue';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';
import { useWidgetAttributes } from '../../provider/widgetState/widgetAttributes/useWidgetAttributes';
import { NetworkCategory } from '../../../types/NetworkCategory';

/**
 * ウィジェット(ブロック)の状態を初期化します。
 */
export const useInitWidgetState = () => {
	// 選択されているネットワークの初期化
	useInitSelectedNetwork();

	// 入力されている価格の初期化
	useInitPriceValue();

	// 選択されている通貨シンボルの初期化
	useInitSelectedPriceSymbol();
};

/**
 * 画面で選択されているネットワークを初期化します。
 */
const useInitSelectedNetwork = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes } = useWidgetAttributes();

	// ユーザーが選択したネットワーク
	const { selectedNetworkCategory, setSelectedNetworkCategory } = useSelectedNetworkCategory();

	useEffect( () => {
		if ( selectedNetworkCategory === undefined ) {
			if ( widgetAttributes.sellingNetworkCategoryID === null ) {
				setSelectedNetworkCategory( null );
			} else {
				setSelectedNetworkCategory( NetworkCategory.from( widgetAttributes.sellingNetworkCategoryID ) );
			}
		}
	}, [ widgetAttributes, selectedNetworkCategory, setSelectedNetworkCategory ] );
};

/**
 * 画面で入力されている価格を初期化します。
 */
const useInitPriceValue = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes } = useWidgetAttributes();

	// ユーザーが入力した価格
	const { inputPriceValue, setInputPriceValue } = useInputPriceValue();

	useEffect( () => {
		if ( inputPriceValue === undefined ) {
			const { sellingAmountHex, sellingDecimals } = widgetAttributes;
			const inputValue = amountToInputValue( sellingAmountHex, sellingDecimals );
			setInputPriceValue( inputValue );
		}
	}, [ widgetAttributes, inputPriceValue, setInputPriceValue ] );
};

/**
 * 画面で選択されている通貨シンボルを初期化します。
 */
const useInitSelectedPriceSymbol = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes } = useWidgetAttributes();

	// ユーザーが選択した通貨シンボル
	const { selectedPriceSymbol, setSelectedPriceSymbol } = useSelectedPriceSymbol();

	useEffect( () => {
		if ( selectedPriceSymbol === undefined ) {
			setSelectedPriceSymbol( widgetAttributes.sellingSymbol );
		}
	}, [ widgetAttributes, selectedPriceSymbol, setSelectedPriceSymbol ] );
};
