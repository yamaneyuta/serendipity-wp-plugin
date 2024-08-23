import { createContext, useState } from 'react';

type InputPriceAmountContextType = ReturnType< typeof _useInputPriceAmount >;

export const InputPriceAmountContext = createContext< InputPriceAmountContextType | undefined >( undefined );

const _useInputPriceAmount = () => {
	// 価格(0xから開始する16進数の文字列)
	const [ inputAmount, _setInputAmount ] = useState< string | null | undefined >( undefined );

	// 価格の小数点以下の桁数
	const [ inputDecimals, _setInputDecimals ] = useState< number | null | undefined >( undefined );

	// 価格を更新する関数
	const setInputPriceAmount = ( amount: string, decimals: number ) => {
		_setInputAmount( amount );
		_setInputDecimals( decimals );
	};

	return {
		inputAmount,
		inputDecimals,
		setInputPriceAmount,
	};
};

type InputPriceAmountProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが入力した価格をamount(16進数の数量)とdecimals(小数点以下桁数)で保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const InputPriceAmountProvider: React.FC< InputPriceAmountProviderProps > = ( { children } ) => {
	const value = _useInputPriceAmount();
	return <InputPriceAmountContext.Provider value={ value }>{ children }</InputPriceAmountContext.Provider>;
};
