import { useContext } from 'react';
import { PostSettingContext } from './PostSettingProvider';

export const usePostSetting = () => {
	const context = useContext( PostSettingContext );
	if ( ! context ) {
		throw new Error( '{78985761-02F3-4AE8-B26A-08D63BBF8AE5}' );
	}
	return context;
};
