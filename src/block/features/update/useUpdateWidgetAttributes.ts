import { useEffect } from 'react';
import { inputValueToAmount } from '@yamaneyuta/serendipity-lib-js-price-format';
import { useSelectedNetwork } from '../../provider/widgetState/selectedNetwork/useSelectedNetwork';
import { useWidgetAttributes } from '../../provider/widgetState/widgetAttributes/useWidgetAttributes';
import { useInputPriceValue } from '../../provider/widgetState/inputPriceValue/useInputPriceValue';
import { WidgetAttributes } from '../../types/WidgetAttributes';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';

/**
 * 画面の状態が変更された際に、HTMLコメントとして登録されるブロックの属性を更新します。
 */
export const useUpdateWidgetAttributes = () => {
	// 販売ネットワークの更新
	useUpdateSellingNetworkAttribute();

	// 販売価格の更新
	useUpdatePriceValueAttribute();

	// 通貨シンボルの更新
	useUpdatePriceSymbolAttribute();
};

/**
 * ブロックの属性として保存される、販売ネットワークの値を更新します。
 */
const useUpdateSellingNetworkAttribute = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes, setWidgetAttributes } = useWidgetAttributes();

	// ユーザーが選択したネットワーク
	const { selectedNetwork } = useSelectedNetwork();

	useEffect( () => {
		// 値が変更されている場合は更新
		if ( selectedNetwork !== undefined && widgetAttributes.sellingNetwork !== selectedNetwork ) {
			setWidgetAttributes( {
				...widgetAttributes,
				sellingNetwork: selectedNetwork,
			} );
		}
	}, [ widgetAttributes, setWidgetAttributes, selectedNetwork ] );
};

/**
 * ブロックの属性として保存される、販売価格の値を更新します。
 */
const useUpdatePriceValueAttribute = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes, setWidgetAttributes } = useWidgetAttributes();

	// ユーザーが入力した価格
	const { inputPriceValue } = useInputPriceValue();

	useEffect( () => {
		if ( inputPriceValue === undefined ) {
			return;
		}

		let amountHex = '0x' + 0n.toString( 16 );
		let decimals = 0;

		// ユーザーが入力した価格をamountとdecimalsに変換
		if ( inputPriceValue ) {
			const tmp = inputValueToAmount( inputPriceValue );
			amountHex = '0x' + tmp.amount.toString( 16 );
			decimals = tmp.decimals;
		}

		// 値が変更されている場合は更新
		if ( widgetAttributes.sellingAmountHex !== amountHex || widgetAttributes.sellingDecimals !== decimals ) {
			const newAttributes: WidgetAttributes = structuredClone( widgetAttributes );
			newAttributes.sellingAmountHex = amountHex;
			newAttributes.sellingDecimals = decimals;
			setWidgetAttributes( newAttributes );
		}
	}, [ widgetAttributes, setWidgetAttributes, inputPriceValue ] );
};

/**
 * ブロックの属性として保存される、販売価格の通貨シンボルを更新します。
 */
const useUpdatePriceSymbolAttribute = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes, setWidgetAttributes } = useWidgetAttributes();

	// ユーザーが選択した通貨シンボル
	const { selectedPriceSymbol } = useSelectedPriceSymbol();

	useEffect( () => {
		// 値が変更されている場合は更新
		if ( selectedPriceSymbol !== undefined && widgetAttributes.sellingSymbol !== selectedPriceSymbol ) {
			const newAttributes: WidgetAttributes = structuredClone( widgetAttributes );
			newAttributes.sellingSymbol = selectedPriceSymbol;
			setWidgetAttributes( newAttributes );
		}
	}, [ widgetAttributes, setWidgetAttributes, selectedPriceSymbol ] );
};
