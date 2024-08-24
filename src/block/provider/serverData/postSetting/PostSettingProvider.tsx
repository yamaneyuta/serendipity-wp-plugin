import { createContext, useCallback } from 'react';
import {
	PostSettingInput,
	PostSettingQuery,
	usePostSettingQuery,
	useSetPostSettingMutation,
} from '../../../../types/gql/generated';
import { usePostID } from '../../postID/usePostID';

type PostSettingType = ReturnType< typeof _usePostSetting >;

export const PostSettingContext = createContext< PostSettingType | undefined >( undefined );

const _usePostSetting = () => {
	const postID = usePostID();
	const { data, refetch } = usePostSettingQuery( { postID } );
	checkPostSetting( data ); // データの整合性チェック
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
		postSetting: data,
		savePostSetting,
	};
};

type PostSettingProviderProps = {
	children: React.ReactNode;
};

/**
 * サーバーから受信したデータの整合性をチェックし、問題があればエラーをスローします。
 * @param postSetting
 */
const checkPostSetting = ( postSetting: PostSettingQuery | undefined ) => {
	if ( postSetting ) {
		if ( ! postSetting.mainnetSellableSymbols === undefined ) {
			throw new Error( '[1ED2539F] mainnetSellableSymbols is not defined' );
		}
		if ( ! postSetting.testnetSellableSymbols === undefined ) {
			throw new Error( '[7F7A9241] testnetSellableSymbols is not defined' );
		}
		if ( postSetting.privatenetSellableSymbols === undefined ) {
			throw new Error( '[B1F3CD99] privatenetSellableSymbols is not defined' );
		}
	}
};

export const PostSettingProvider: React.FC< PostSettingProviderProps > = ( { children } ) => {
	const value = _usePostSetting();
	return <PostSettingContext.Provider value={ value }>{ children }</PostSettingContext.Provider>;
};
