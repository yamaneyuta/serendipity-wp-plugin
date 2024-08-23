import { useContext } from 'react';
import { SelectedPriceSymbolContext } from './SelectedPriceSymbolProvider';

/**
 * ユーザーが選択した販売価格の通貨シンボルを取得または設定する機能を提供します。
 */
export const useSelectedPriceSymbol = () => {
	const context = useContext( SelectedPriceSymbolContext );
	if ( ! context ) {
		throw new Error( '[DBB8277B] Context is not found' );
	}

	return context;
};
