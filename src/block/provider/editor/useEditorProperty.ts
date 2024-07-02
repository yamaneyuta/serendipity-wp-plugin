import { useContext } from 'react';
import { BlockEditorPropertyContext } from './BlockEditorPropertyProvider';

export const useEditorProperty = () => {
	const context = useContext( BlockEditorPropertyContext );
	if ( ! context ) {
		throw new Error( '{B6E007E0-D092-47A6-BC79-0E14BC62F0D6}' );
	}
	return context;
};
