import assert from 'assert';
import { useContext } from 'react';
import { PostSettingContext } from './PostSettingProvider';

/**
 * サーバーに保存されている投稿設定を取得します。
 */
export const usePostSetting = () => {
	const context = useContext( PostSettingContext );
	assert( context, '[78985761] Context is not found' );

	return context.postSetting;
};
