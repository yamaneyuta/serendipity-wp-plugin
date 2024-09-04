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
			// 画面初期化中の場合
			return undefined;
		}
		if ( sellingNetwork === null ) {
			// 販売ネットワークが未指定の場合
			return null;
		}
		// 指定されたネットワークで販売可能な通貨シンボル一覧を取得
		// (販売可能な通貨シンボル一覧をAPIから取得できていない状態の場合はundefinedが返る)
		return getSellableSymbols( sellingNetwork );
	}, [ sellingNetwork, getSellableSymbols ] );
};
