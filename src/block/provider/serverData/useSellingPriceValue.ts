import { usePostSetting } from './postSetting/usePostSetting';

/**
 * サーバーに記録されている販売価格のamount及びdecimalsを取得します。
 */
export const useSellingPriceValue = () => {
	const sellingPrice = usePostSetting()?.sellingPrice;

	const amountHex = sellingPrice ? sellingPrice.amountHex : sellingPrice;
	const decimals = sellingPrice ? sellingPrice.decimals : sellingPrice;

	return {
		amountHex,
		decimals,
	};
};
