import { useContext } from 'react';
import { PostSettingContext } from './PostSettingProvider';

/**
 * サーバーに保存されている投稿設定を取得します。
 */
export const usePostSetting = () => {
	const context = useContext( PostSettingContext );
	if ( ! context ) {
		throw new Error( '{78985761-02F3-4AE8-B26A-08D63BBF8AE5}' );
	}

	const postSetting = context.postSetting;

	return postSetting;
};
