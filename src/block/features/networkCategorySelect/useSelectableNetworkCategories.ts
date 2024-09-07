import { useMemo } from 'react';
import { usePostSetting } from '../../provider/serverData/postSetting/usePostSetting';
import { NetworkCategory } from '../../../types/NetworkCategory';

/**
 * 投稿編集画面で選択可能なネットワークカテゴリ一覧を取得します。
 */
export const useSelectableNetworkCategories = () => {
	const serverPostSetting = usePostSetting();

	return useMemo( () => {
		if ( serverPostSetting === undefined ) {
			// 読み込み中
			return undefined;
		}

		const networks: NetworkCategory[] = []; // 戻り値となるネットワークカテゴリ一覧

		// 各ネットワークで販売可能な通貨シンボルが存在する場合は、ネットワークカテゴリ一覧に追加
		if ( serverPostSetting.mainnetSellableSymbols ) {
			networks.push( NetworkCategory.mainnet() );
		}
		if ( serverPostSetting.testnetSellableSymbols ) {
			networks.push( NetworkCategory.testnet() );
		}
		if ( serverPostSetting.privatenetSellableSymbols ) {
			networks.push( NetworkCategory.privatenet() );
		}

		return networks;
	}, [ serverPostSetting ] );
};
