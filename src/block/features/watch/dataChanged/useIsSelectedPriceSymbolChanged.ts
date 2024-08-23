import { useSellingPriceSymbol } from '../../../provider/serverData/useSellingPriceSymbol';
import { useSelectedPriceSymbol } from '../../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol';

/**
 * 販売価格の通貨シンボルが画面上で変更されたかどうかを取得します。
 */
export const useIsSelectedPriceSymbolChanged = () => {
	// サーバーから取得した販売価格の通貨シンボル
	const sellingPriceSymbol = useSellingPriceSymbol();
	// ユーザーが選択した販売価格の通貨シンボル
	const selectedPriceSymbol = useSelectedPriceSymbol().selectedPriceSymbol;

	// 読み込み中はデータが変更されたとみなさない
	if ( sellingPriceSymbol === undefined || selectedPriceSymbol === undefined ) {
		return false;
	}

	return sellingPriceSymbol !== selectedPriceSymbol;
};
