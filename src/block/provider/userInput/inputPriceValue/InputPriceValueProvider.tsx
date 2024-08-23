import { createContext, useState } from 'react';
import { Assert } from '../../../lib/Assert';

type InputPriceValueContextType = ReturnType< typeof _useInputPriceValue >;

export const InputPriceValueContext = createContext< InputPriceValueContextType | undefined >( undefined );

const _useInputPriceValue = () => {
	// 価格(0xから開始する16進数の文字列)
	const [ inputAmountHex, _setInputAmountHex ] = useState< string | null | undefined >( undefined );

	// 価格の小数点以下の桁数
	const [ inputDecimals, _setInputDecimals ] = useState< number | null | undefined >( undefined );

	// 価格を更新する関数
	const setInputPriceValue = ( amountHex: string | null | undefined, decimals: number | null | undefined ) => {
		// 引数のチェック
		if ( amountHex ) {
			Assert.isAmountHex( amountHex );
		}
		if ( typeof decimals === 'number' ) {
			Assert.isDecimals( decimals );
		}

		// stateを更新
		_setInputAmountHex( amountHex );
		_setInputDecimals( decimals );
	};

	return {
		inputAmountHex,
		inputDecimals,
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
