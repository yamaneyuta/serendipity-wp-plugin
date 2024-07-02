import { useContext } from 'react';
import { PostSettingContext } from './PostSettingProvider';

export const usePostSetting = () => {
	const context = useContext( PostSettingContext );
	if ( ! context ) {
		throw new Error( '{A8C089B2-824F-4BA5-8120-7FBFE42F36B2}' );
	}
	return context.savePostSetting;
};
