import { useIsSellingNetworkChanged } from './dataChanged/useIsSellingNetworkChanged';
import { useIsSellingPriceValueChanged } from './dataChanged/useIsSellingPriceValueChanged';
import { useIsSellingPriceSymbolChanged } from './dataChanged/useIsSellingPriceSymbolChanged';

export const useIsDataChanged = () => {
	// 販売ネットワークが変更されたかどうか
	const isSellingNetworkChanged = useIsSellingNetworkChanged();

	// 販売価格の値が変更されたかどうか
	const isSellingPriceValueChanged = useIsSellingPriceValueChanged();

	// 販売価格の通貨シンボルが変更されたかどうか
	const isSellingPriceSymbolChanged = useIsSellingPriceSymbolChanged();

	// いずれかが変更されている場合はtrueを返す
	return isSellingNetworkChanged || isSellingPriceValueChanged || isSellingPriceSymbolChanged;
};
