import { useMemo } from 'react';
import { NetworkType } from '../../../types/gql/generated';
import { useGetSellableSymbolsCallback } from '../../provider/serverData/useGetSellableSymbolsCallback';

/**
 * 画面で選択可能な通貨シンボル一覧を取得します。
 * @param sellingNetwork
 */
export const useSelectableSymbols = ( sellingNetwork: NetworkType | null | undefined ): string[] | null | undefined => {
	const getSellableSymbols = useGetSellableSymbolsCallback();

	return useMemo( () => {
		if ( sellingNetwork === undefined ) {
			// データ取得中の場合
			return undefined;
		}
		if ( sellingNetwork === null ) {
			// 販売ネットワークが未指定の場合
			return null;
		}

		const selectableSymbols = getSellableSymbols( sellingNetwork );

		// APIの仕様上、販売ネットワークをサーバーから取得すると同時に販売可能な通貨シンボルも取得するため、
		// selectableSymbolsはundefinedにはならない
		if ( selectableSymbols === undefined ) {
			throw new Error( '[FC51AFA9] selectableSymbols is undefined. - sellingNetwork: ' + sellingNetwork );
		}

		return selectableSymbols;
	}, [ sellingNetwork, getSellableSymbols ] );
};
