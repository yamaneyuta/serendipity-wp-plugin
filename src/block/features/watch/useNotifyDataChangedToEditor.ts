import { useEffect } from 'react';
import { useIsDataChanged } from './useIsDataChanged';

/**
 * ユーザーによってブロックのデータが変更されたときに、WordPressのエディタにデータ変更を通知するフック
 *
 * 変更を通知することによって、保存ボタンが押せるようになります。
 *
 * @param onDataChangedCallback WordPressのエディタにデータ変更を通知するコールバック
 */
export const useNotifyDataChangedToEditor = ( onDataChangedCallback: () => void ) => {
	// データが変更されたかどうかを取得
	const isDataChanged = useIsDataChanged();

	// ユーザーによってデータが更新された時にWordPressのエディタに通知
	useEffect( () => {
		if ( isDataChanged ) {
			onDataChangedCallback();
		}
	}, [ onDataChangedCallback, isDataChanged ] );
};
