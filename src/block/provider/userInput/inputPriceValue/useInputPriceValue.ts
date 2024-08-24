import assert from 'assert';
import { useContext } from 'react';
import { InputPriceValueContext } from './InputPriceValueProvider';

/**
 * ユーザーが入力した価格を取得または設定する機能を提供します。
 */
export const useInputPriceValue = () => {
	const context = useContext( InputPriceValueContext );
	assert( context, '[A09C1515] Context is not found' );

	return context;
};
