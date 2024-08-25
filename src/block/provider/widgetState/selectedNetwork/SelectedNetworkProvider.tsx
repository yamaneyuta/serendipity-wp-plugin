import { createContext, useState } from 'react';
import { NetworkType } from '../../../../types/gql/generated';

type SelectedNetworkContextType = ReturnType< typeof _useSelectedNetwork >;

export const SelectedNetworkContext = createContext< SelectedNetworkContextType | undefined >( undefined );

const _useSelectedNetwork = () => {
	const [ selectedNetwork, setSelectedNetwork ] = useState< NetworkType | null | undefined >( undefined );
	return {
		selectedNetwork,
		setSelectedNetwork,
	};
};

type SelectedNetworkProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択したネットワークを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SelectedNetworkProvider: React.FC< SelectedNetworkProviderProps > = ( { children } ) => {
	const value = _useSelectedNetwork();
	return <SelectedNetworkContext.Provider value={ value }>{ children }</SelectedNetworkContext.Provider>;
};
