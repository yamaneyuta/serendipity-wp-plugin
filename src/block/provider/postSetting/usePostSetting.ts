import { useContext } from 'react';
import { PostSettingContext } from './PostSettingProvider';
import { PostSettingQuery } from '../../../types/gql/generated';

/**
 * サーバーに保存されている投稿設定を取得します。
 */
export const usePostSetting = () => {
	const context = useContext( PostSettingContext );
	if ( ! context ) {
		throw new Error( '{78985761-02F3-4AE8-B26A-08D63BBF8AE5}' );
	}

	const postSetting = context.postSetting;

	if ( postSetting ) {
		if ( ! postSetting.mainnetSellableSymbols === undefined ) {
			throw new Error( '[1ED2539F] mainnetSellableSymbols is not defined' );
		}
		if ( ! postSetting.testnetSellableSymbols === undefined ) {
			throw new Error( '[7F7A9241] testnetSellableSymbols is not defined' );
		}
		if ( postSetting.privatenetSellableSymbols === undefined ) {
			throw new Error( '[B1F3CD99] privatenetSellableSymbols is not defined' );
		}
	}

	return postSetting as
		| ( PostSettingQuery & {
				mainnetSellableSymbols: Array< string > | null;
				testnetSellableSymbols: Array< string > | null;
				privatenetSellableSymbols: Array< string > | null;
		  } )
		| undefined;
};
