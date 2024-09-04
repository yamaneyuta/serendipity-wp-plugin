import { useEffect } from 'react';
import { amountToInputValue } from '@yamaneyuta/serendipity-lib-js-price-format';
import { useSelectedNetwork } from '../../provider/widgetState/selectedNetwork/useSelectedNetwork';
import { WidgetAttributes } from '../../types/WidgetAttributes';
import { NetworkType } from '../../../types/gql/generated';
import { useInputPriceValue } from '../../provider/widgetState/inputPriceValue/useInputPriceValue';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';

/**
 * ウィジェット(ブロック)の状態を初期化します。
 * @param attributes
 */
export const useInitWidgetState = ( attributes: Readonly< WidgetAttributes > ) => {
	// 選択されているネットワークの初期化
	useInitSelectedNetwork( attributes );

	// 入力されている価格の初期化
	useInitPriceValue( attributes );

	// 選択されている通貨シンボルの初期化
	useInitSelectedPriceSymbol( attributes );
};

/**
 * 画面で選択されているネットワークを初期化します。
 * @param attributes
 */
const useInitSelectedNetwork = ( attributes: Readonly< WidgetAttributes > ) => {
	// ユーザーが選択したネットワーク
	const { selectedNetwork, setSelectedNetwork } = useSelectedNetwork();

	useEffect( () => {
		if ( selectedNetwork === undefined ) {
			// TODO: キャストを修正
			setSelectedNetwork( attributes.sellingNetworkCategory as NetworkType | null );
		}
	}, [ attributes, selectedNetwork, setSelectedNetwork ] );
};

/**
 * 画面で入力されている価格を初期化します。
 * @param attributes
 */
const useInitPriceValue = ( attributes: Readonly< WidgetAttributes > ) => {
	// ユーザーが入力した価格
	const { inputPriceValue, setInputPriceValue } = useInputPriceValue();

	useEffect( () => {
		if ( inputPriceValue === undefined ) {
			const { amountHex, decimals } = attributes.sellingPrice;
			const inputValue = amountToInputValue( amountHex, decimals );
			setInputPriceValue( inputValue );
		}
	}, [ attributes, inputPriceValue ] );
};

/**
 * 画面で選択されている通貨シンボルを初期化します。
 * @param attributes
 */
const useInitSelectedPriceSymbol = ( attributes: Readonly< WidgetAttributes > ) => {
	// ユーザーが選択した通貨シンボル
	const { selectedPriceSymbol, setSelectedPriceSymbol } = useSelectedPriceSymbol();

	useEffect( () => {
		if ( selectedPriceSymbol === undefined ) {
			setSelectedPriceSymbol( attributes.sellingPrice.symbol );
		}
	}, [ attributes, selectedPriceSymbol, setSelectedPriceSymbol ] );
};
