import { useIsSellingPriceSymbolChanged } from './dataChanged/useIsSellingPriceSymbolChanged';

export const useIsDataChanged = () => {
	// 販売価格の通貨シンボルが変更されたかどうか
	const isSellingPriceSymbolChanged = useIsSellingPriceSymbolChanged();

	return isSellingPriceSymbolChanged;
};
