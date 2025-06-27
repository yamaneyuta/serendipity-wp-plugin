import { createContext, useEffect, useState } from 'react';
import { usePostSettingMutation } from '../../../../types/gql/generated';

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

/**
 * `PostSetting`がQueryからMutationに変更されたので代替のhooks
 */
const usePostSettingQuery = () => {
	const { mutateAsync } = usePostSettingMutation();
	const [ data, setData ] = useState< Awaited< ReturnType< typeof mutateAsync > > | undefined >( undefined );
	useEffect( () => {
		mutateAsync( {} ).then( setData );
	}, [ mutateAsync, setData ] );

	return {
		data,
	};
};
