import { useContext } from 'react';
import { InputPriceAmountContext } from './InputPriceAmountProvider';

/**
 * ユーザーが入力した価格を取得または設定する機能を提供します。
 */
export const useInputPriceAmount = () => {
	const context = useContext( InputPriceAmountContext );
	if ( ! context ) {
		throw new Error( '[A09C1515] Context is not found' );
	}

	return context;
};
