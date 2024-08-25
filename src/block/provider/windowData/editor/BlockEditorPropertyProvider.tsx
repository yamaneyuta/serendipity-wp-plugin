import { subscribe, select } from '@wordpress/data';
import { createContext, useEffect, useState } from 'react';

type BlockEditorPropertyType = ReturnType< typeof _useEditorProperty >;

export const BlockEditorPropertyContext = createContext< BlockEditorPropertyType | undefined >( undefined );

const _useEditorProperty = () => {
	const [ state, setState ] = useState< {
		isSaving: boolean;
		isAutosavingPost: boolean;
	} >( { isSaving: false, isAutosavingPost: false } );

	useEffect( () => {
		const unsubscribe = subscribe( () => {
			const isSaving = select( 'core/editor' ).isSavingPost();
			const isAutosavingPost = select( 'core/editor' ).isAutosavingPost();
			setState( { isSaving, isAutosavingPost } );
		} );
		return unsubscribe;
	}, [] );

	return state;
};

type BlockEditorPropertyProviderProps = {
	children: React.ReactNode;
};

export const BlockEditorPropertyProvider: React.FC< BlockEditorPropertyProviderProps > = ( { children } ) => {
	const value = _useEditorProperty();
	return <BlockEditorPropertyContext.Provider value={ value }>{ children }</BlockEditorPropertyContext.Provider>;
};
