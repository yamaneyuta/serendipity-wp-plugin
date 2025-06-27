import { createContext, useState } from 'react';

type SelectedPriceSymbolContextType = ReturnType< typeof _useSelectedPriceSymbol >;

export const SelectedPriceSymbolContext = createContext< SelectedPriceSymbolContextType | undefined >( undefined );

const _useSelectedPriceSymbol = () => {
	const [ selectedPriceSymbol, setSelectedPriceSymbol ] = useState< string | null | undefined >( undefined );
	return {
		selectedPriceSymbol,
		setSelectedPriceSymbol,
	};
};

type SelectedPriceSymbolProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択した販売価格の通貨シンボルを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SelectedPriceSymbolProvider: React.FC< SelectedPriceSymbolProviderProps > = ( { children } ) => {
	const value = _useSelectedPriceSymbol();
	return <SelectedPriceSymbolContext.Provider value={ value }>{ children }</SelectedPriceSymbolContext.Provider>;
};
