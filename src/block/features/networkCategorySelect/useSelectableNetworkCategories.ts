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

		return serverPostSetting.networkCategories.map( ( networkCategory ) =>
			NetworkCategory.from( networkCategory.id )
		);
	}, [ serverPostSetting ] );
};
