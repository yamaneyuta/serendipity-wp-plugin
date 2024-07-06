import { useMemo } from 'react';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';
import type { ScreenPostSetting } from './ScreenPostSetting.type';

/**
 * 画面上で保持する設定情報をサーバーから取得します。
 */
export const useScreenPostSetting = (): ScreenPostSetting => {
	const postSetting = usePostSetting();

	return useMemo( () => {
		if ( postSetting === undefined ) {
			// 読み込み中
			return {};
		} else if ( postSetting.sellingPrice === null ) {
			// サーバーに登録済みデータがない時
			const symbol = postSetting.sellableSymbols.length > 0 ? postSetting.sellableSymbols[ 0 ] : null;
			return {
				sellingPrice: {
					// データが存在しない場合は数量の部分を0で初期化
					amountHex: '0x00',
					decimals: 0,
					symbol,
				},
			};
		}
		// 新しいオブジェクトを生成して返す
		return JSON.parse( JSON.stringify( postSetting ) );
	}, [ postSetting ] );
};
