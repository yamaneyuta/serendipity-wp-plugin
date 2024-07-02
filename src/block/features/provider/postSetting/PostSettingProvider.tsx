import { createContext, useCallback, useEffect, useState } from 'react';
import { PostSettingInput, usePostSettingQuery, useSetPostSettingMutation } from '../../../../types/gql/generated';
import { usePostID } from '../postID/usePostID';

type PostSettingType = ReturnType< typeof _usePostSetting >;

export const PostSettingContext = createContext< PostSettingType | undefined >( undefined );

const _usePostSetting = () => {
	const postID = usePostID();
	const { data, refetch } = usePostSettingQuery( { postID } );
	const { mutateAsync } = useSetPostSettingMutation( {
		onSuccess: async () => {
			await refetch();
		},
	} );

	const save = async ( postSetting: PostSettingInput ) => {
		console.log( 'save', postSetting );

		await mutateAsync( {
			postID,
			postSetting,
		} );
	};

	return {
		postSetting: data?.postSetting,
		save,
	};
};

type PostSettingProviderProps = {
	children: React.ReactNode;
};

export const PostSettingProvider: React.FC< PostSettingProviderProps > = ( { children } ) => {
	const value = _usePostSetting();
	return <PostSettingContext.Provider value={ value }>{ children }</PostSettingContext.Provider>;
};

type ScreenPostSetting = {};
