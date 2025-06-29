import { useEffect } from 'react';
import { useSelectedNetworkCategory } from '../../provider/widgetState/selectedNetworkCategory/useSelectedNetworkCategory';
import { useWidgetAttributes } from '../../provider/widgetState/widgetAttributes/useWidgetAttributes';
import { useInputPriceValue } from '../../provider/widgetState/inputPriceValue/useInputPriceValue';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';
import { NetworkCategory } from '../../../types/NetworkCategory';
import { WidgetAttributes } from '../../types/WidgetAttributes';

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
 * ブロックの属性として保存される、販売ネットワークカテゴリの値を更新します。
 */
const useUpdateSellingNetworkAttribute = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes, setWidgetAttributes } = useWidgetAttributes();

	// ユーザーが選択したネットワークカテゴリ
	const { selectedNetworkCategory } = useSelectedNetworkCategory();

	useEffect( () => {
		// 値が変更されている場合は属性を更新
		if ( selectedNetworkCategory === undefined ) {
			return;
		}
		const orgSelectedNetworkCategory =
			widgetAttributes.sellingNetworkCategoryID === null
				? null
				: NetworkCategory.from( widgetAttributes.sellingNetworkCategoryID );

		if ( orgSelectedNetworkCategory !== selectedNetworkCategory && selectedNetworkCategory ) {
			setWidgetAttributes( ( s ) => ( { ...s, sellingNetworkCategoryID: selectedNetworkCategory.id() } ) );
		}
	}, [ widgetAttributes, setWidgetAttributes, selectedNetworkCategory ] );
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

		// 値が変更されている場合は属性を更新
		if ( widgetAttributes.sellingAmount !== inputPriceValue ) {
			setWidgetAttributes( ( s ): WidgetAttributes => ( { ...s, sellingAmount: inputPriceValue } ) );
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
		// 値が変更されている場合は属性を更新
		if ( selectedPriceSymbol !== undefined && widgetAttributes.sellingSymbol !== selectedPriceSymbol ) {
			setWidgetAttributes( ( s ) => ( { ...s, sellingSymbol: selectedPriceSymbol } ) );
		}
	}, [ widgetAttributes, setWidgetAttributes, selectedPriceSymbol ] );
};
