import { useMemo } from 'react';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';
import type { ScreenPostSetting } from './ScreenPostSetting.type';

/**
 * 画面上で保持する設定情報をサーバーから取得します。
 */
export const useScreenPostSetting = (): ScreenPostSetting => {
	const postSetting: ScreenPostSetting | null | undefined = usePostSetting();

	return useMemo( () => {
		if ( postSetting === undefined ) {
			// 読み込み中
			return {};
		} else if ( postSetting === null ) {
			// サーバーに登録済みデータがない
			return {
				sellingPrice: {
					// データが存在しない場合は数量の部分を0で初期化
					amountHex: '0x00',
					decimals: 0,
				},
			};
		}
		// 新しいオブジェクトを生成して返す
		return JSON.parse( JSON.stringify( postSetting ) );
	}, [ postSetting ] );
};
