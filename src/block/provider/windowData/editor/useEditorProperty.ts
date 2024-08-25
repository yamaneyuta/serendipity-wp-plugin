import assert from 'assert';
import { useContext } from 'react';
import { BlockEditorPropertyContext } from './BlockEditorPropertyProvider';

export const useEditorProperty = () => {
	const context = useContext( BlockEditorPropertyContext );
	assert( context, '[B6E007E0] Context is not found' );

	return context;
};
