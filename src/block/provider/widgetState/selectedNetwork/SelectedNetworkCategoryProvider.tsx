import { createContext, useState } from 'react';
import { NetworkCategory } from '../../../../types/NetworkCategory';

type SelectedNetworkCategoryContextType = ReturnType< typeof _useSelectedNetworkCategory >;

export const SelectedNetworkCategoryContext = createContext< SelectedNetworkCategoryContextType | undefined >(
	undefined
);

const _useSelectedNetworkCategory = () => {
	const [ selectedNetworkCategory, setSelectedNetworkCategory ] = useState< NetworkCategory | null | undefined >(
		undefined
	);
	return {
		selectedNetworkCategory,
		setSelectedNetworkCategory,
	};
};

type SelectedNetworkCategoryProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択したネットワークカテゴリを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SelectedNetworkCategoryProvider: React.FC< SelectedNetworkCategoryProviderProps > = ( { children } ) => {
	const value = _useSelectedNetworkCategory();
	return (
		<SelectedNetworkCategoryContext.Provider value={ value }>{ children }</SelectedNetworkCategoryContext.Provider>
	);
};
