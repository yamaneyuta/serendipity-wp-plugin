import assert from 'assert';
import { createContext } from 'react';
import { PostSettingQuery, usePostSettingQuery } from '../../../../types/gql/generated';

type PostSettingType = ReturnType< typeof _usePostSetting >;

export const PostSettingContext = createContext< PostSettingType | undefined >( undefined );

const _usePostSetting = () => {
	const { data } = usePostSettingQuery();
	checkPostSetting( data ); // データの整合性チェック

	return {
		postSetting: data,
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
		const { mainnetSellableSymbols, testnetSellableSymbols, privatenetSellableSymbols } = postSetting;

		assert( mainnetSellableSymbols !== undefined, '[1ED2539F] mainnetSellableSymbols is not defined' );
		assert( testnetSellableSymbols !== undefined, '[7F7A9241] testnetSellableSymbols is not defined' );
		assert( privatenetSellableSymbols !== undefined, '[B1F3CD99] privatenetSellableSymbols is not defined' );
	}
};

export const PostSettingProvider: React.FC< PostSettingProviderProps > = ( { children } ) => {
	const value = _usePostSetting();
	return <PostSettingContext.Provider value={ value }>{ children }</PostSettingContext.Provider>;
};
