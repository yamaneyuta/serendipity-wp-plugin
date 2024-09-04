import { useEffect } from 'react';
import { amountToInputValue } from '@yamaneyuta/serendipity-lib-js-price-format';
import { useSelectedNetwork } from '../../provider/widgetState/selectedNetwork/useSelectedNetwork';
import { NetworkType } from '../../../types/gql/generated';
import { useInputPriceValue } from '../../provider/widgetState/inputPriceValue/useInputPriceValue';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';
import { useWidgetAttributes } from '../../provider/widgetState/widgetAttributes/useWidgetAttributes';

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
	const { selectedNetwork, setSelectedNetwork } = useSelectedNetwork();

	useEffect( () => {
		if ( selectedNetwork === undefined ) {
			// TODO: キャストを修正
			setSelectedNetwork( widgetAttributes.sellingNetworkCategory as NetworkType | null );
		}
	}, [ widgetAttributes, selectedNetwork, setSelectedNetwork ] );
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
			const { amountHex, decimals } = widgetAttributes.sellingPrice;
			const inputValue = amountToInputValue( amountHex, decimals );
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
			setSelectedPriceSymbol( widgetAttributes.sellingPrice.symbol );
		}
	}, [ widgetAttributes, selectedPriceSymbol, setSelectedPriceSymbol ] );
};
