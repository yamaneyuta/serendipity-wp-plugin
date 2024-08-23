import { ScreenPostSetting } from './ScreenPostSetting.type';
import { useScreenPostSetting } from './useScreenPostSetting';
import equal from 'fast-deep-equal';

/**
 * ユーザーが画面操作によって設定を変更したかどうかを取得します。
 * @param postSetting
 * @deprecated
 */
export const useIsScreenDataChanged = ( postSetting: ScreenPostSetting ) => {
	const serverData = useScreenPostSetting();

	// サーバーからデータを取得中の場合はfalse(変更なし)として扱う
	if ( postSetting.sellingPrice === undefined || serverData.sellingPrice === undefined ) {
		return false;
	}

	return ! equal( serverData, postSetting );
};
