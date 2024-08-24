import assert from 'assert';
import { useContext } from 'react';
import { PostSettingContext } from './PostSettingProvider';

export const useSavePostSettingCallback = () => {
	const context = useContext( PostSettingContext );
	assert( context, '[A8C089B2] Context is not found' );

	return context.savePostSetting;
};
