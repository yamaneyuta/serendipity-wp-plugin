import assert from 'assert';
import { createContext } from 'react';
import { PostSettingQuery, usePostSettingQuery } from '../../../../types/gql/generated';

type PostSettingType = ReturnType< typeof _usePostSetting >;

export const PostSettingContext = createContext< PostSettingType | undefined >( undefined );

const _usePostSetting = () => {
	const { data } = usePostSettingQuery();

	return {
		postSetting: data,
	};
};

type PostSettingProviderProps = {
	children: React.ReactNode;
};

export const PostSettingProvider: React.FC< PostSettingProviderProps > = ( { children } ) => {
	const value = _usePostSetting();
	return <PostSettingContext.Provider value={ value }>{ children }</PostSettingContext.Provider>;
};
