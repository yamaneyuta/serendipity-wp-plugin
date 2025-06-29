import { createContext, useState } from 'react';

type InputPriceValueContextType = ReturnType< typeof _useInputPriceValue >;

export const InputPriceValueContext = createContext< InputPriceValueContextType | undefined >( undefined );

const _useInputPriceValue = () => {
	// 価格(表示用) 10進数で画面上に表示される値
	const [ inputPriceValue, setInputPriceValue ] = useState< string | null | undefined >( undefined );

	return {
		inputPriceValue,
		setInputPriceValue,
	};
};

type InputPriceValueProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが入力した価格をamount(16進数の数量)とdecimals(小数点以下桁数)で保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const InputPriceValueProvider: React.FC< InputPriceValueProviderProps > = ( { children } ) => {
	const value = _useInputPriceValue();
	return <InputPriceValueContext.Provider value={ value }>{ children }</InputPriceValueContext.Provider>;
};
