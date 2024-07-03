import { createContext, useCallback } from 'react';
import { PostSettingInput, usePostSettingQuery, useSetPostSettingMutation } from '../../../types/gql/generated';
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

	const savePostSetting = useCallback(
		async ( postSetting: PostSettingInput ) => {
			await mutateAsync( {
				postID,
				postSetting,
			} );
		},
		[ postID, mutateAsync ]
	);

	return {
		postSetting: data?.postSetting,
		savePostSetting,
	};
};

type PostSettingProviderProps = {
	children: React.ReactNode;
};

export const PostSettingProvider: React.FC< PostSettingProviderProps > = ( { children } ) => {
	const value = _usePostSetting();
	return <PostSettingContext.Provider value={ value }>{ children }</PostSettingContext.Provider>;
};
