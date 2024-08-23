import { useSellingPriceValue } from '../../../provider/serverData/useSellingPriceValue';
import { useInputPriceValue } from '../../../provider/userInput/inputPriceValue/useInputPriceValue';

/**
 * 販売価格の数量が画面上で変更されたかどうかを取得します。
 */
export const useIsSellingPriceValueChanged = () => {
	// サーバーから取得した販売価格の数量
	const { amountHex: srvAmountHex, decimals: srvDecimals } = useSellingPriceValue();
	// ユーザーが入力した販売価格の数量
	const { inputAmountHex, inputDecimals } = useInputPriceValue();

	// 読み込み中はデータが変更されたとみなさない
	if ( [ srvAmountHex, srvDecimals, inputAmountHex, inputDecimals ].includes( undefined ) ) {
		return false;
	}

	return srvAmountHex !== inputAmountHex || srvDecimals !== inputDecimals;
};
