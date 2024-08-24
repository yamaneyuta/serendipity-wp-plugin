import { amountToInputValue } from '@yamaneyuta/serendipity-lib-js-price-format';
import { usePostSetting } from './postSetting/usePostSetting';

/**
 * サーバーに記録されている販売価格を10進数の文字列で取得します。
 */
export const useSellingPriceValue = () => {
	const sellingPrice = usePostSetting()?.sellingPrice;

	if ( sellingPrice === undefined ) {
		return undefined;
	}
	if ( sellingPrice === null ) {
		return null;
	}

	const { amountHex, decimals } = sellingPrice;
	return amountToInputValue( amountHex, decimals );
};
