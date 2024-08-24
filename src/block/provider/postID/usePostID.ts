import assert from 'assert';
import { useContext } from 'react';
import { PostIDContext } from './PostIDProvider';

export const usePostID = () => {
	const context = useContext( PostIDContext );
	assert( context, '[4C5A23CD] Context is not found' );

	return context;
};
