import { useSellingPriceValue } from '../../../provider/serverData/useSellingPriceValue';
import { useInputPriceValue } from '../../../provider/widgetState/inputPriceValue/useInputPriceValue';

/**
 * 販売価格の数量が画面上で変更されたかどうかを取得します。
 */
export const useIsSellingPriceValueChanged = () => {
	// サーバーから取得した販売価格の数量
	const sellingPriceValue = useSellingPriceValue();
	// ユーザーが入力した販売価格の数量
	const inputPriceValue = useInputPriceValue().inputPriceValue;

	// 読み込み中はデータが変更されたとみなさない
	if ( sellingPriceValue === undefined || inputPriceValue === undefined ) {
		return false;
	}

	return sellingPriceValue !== inputPriceValue;
};
