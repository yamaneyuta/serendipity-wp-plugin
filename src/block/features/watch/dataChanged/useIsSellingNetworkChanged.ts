import { useSellingNetwork } from '../../../provider/serverData/useSellingNetwork';
import { useSelectedNetwork } from '../../../provider/userInput/selectedNetwork/useSelectedNetwork';

/**
 * 販売するネットワークが画面上で変更されたかどうかを取得します。
 */
export const useIsSellingNetworkChanged = () => {
	// サーバーから取得した販売ネットワーク
	const sellingNetwork = useSellingNetwork();
	// ユーザーが選択したネットワーク
	const selectedNetwork = useSelectedNetwork().selectedNetwork;

	// 読み込み中はデータが変更されたとみなさない
	if ( sellingNetwork === undefined || selectedNetwork === undefined ) {
		return false;
	}

	return sellingNetwork !== selectedNetwork;
};
