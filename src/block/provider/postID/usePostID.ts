import { useContext } from 'react';
import { PostIDContext } from './PostIDProvider';

export const usePostID = () => {
	const context = useContext( PostIDContext );
	if ( ! context ) {
		throw new Error( '{4C5A23CD-A655-4583-BFA2-9E3EFDD593CC}' );
	}
	return context;
};
