import { useMemo } from 'react';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';
import { NetworkType } from '../../../types/gql/generated';

/**
 * 投稿編集画面で選択可能なネットワーク一覧を取得します。
 */
export const useSelectableNetworks = () => {
	const serverPostSetting = usePostSetting();

	return useMemo( () => {
		if ( serverPostSetting === undefined ) {
			// 読み込み中
			return undefined;
		}

		const networks: string[] = []; // 戻り値となるネットワーク一覧

		// 各ネットワークで販売可能な通貨シンボルが存在する場合は、ネットワーク一覧に追加
		if ( serverPostSetting.mainnetSellableSymbols ) {
			networks.push( NetworkType.Mainnet );
		}
		if ( serverPostSetting.testnetSellableSymbols ) {
			networks.push( NetworkType.Testnet );
		}
		if ( serverPostSetting.privatenetSellableSymbols ) {
			networks.push( NetworkType.Privatenet );
		}

		return networks;
	}, [ serverPostSetting ] );
};
