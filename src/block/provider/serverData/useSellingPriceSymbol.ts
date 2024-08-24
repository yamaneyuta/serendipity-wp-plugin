import { usePostSetting } from './postSetting/usePostSetting';

/**
 * サーバーに記録されている販売価格の通貨シンボルを取得します。
 */
export const useSellingPriceSymbol = () => {
	const sellingPrice = usePostSetting()?.sellingPrice;

	const symbol = sellingPrice ? sellingPrice.symbol : sellingPrice;

	return symbol;
};
