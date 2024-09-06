import { useMemo } from 'react';
import { useGetSellableSymbolsCallback } from '../../provider/serverData/useGetSellableSymbolsCallback';
import { useSelectedNetworkCategory } from '../../provider/widgetState/selectedNetworkCategory/useSelectedNetworkCategory';

/**
 * 画面で選択可能な通貨シンボル一覧を取得します。
 */
export const useSelectableSymbols = (): string[] | null | undefined => {
	// 画面で選択されているネットワーク
	const { selectedNetworkCategory } = useSelectedNetworkCategory();
	// ネットワークに応じた販売可能な通貨シンボル一覧を取得するコールバック
	const getSellableSymbols = useGetSellableSymbolsCallback();

	return useMemo( () => {
		if ( selectedNetworkCategory === undefined ) {
			// 画面初期化中の場合
			return undefined;
		}
		if ( selectedNetworkCategory === null ) {
			// 販売ネットワークが未指定の場合
			return null;
		}
		// 指定されたネットワークで販売可能な通貨シンボル一覧を取得
		// (販売可能な通貨シンボル一覧をAPIから取得できていない状態の場合はundefinedが返る)
		return getSellableSymbols( selectedNetworkCategory );
	}, [ selectedNetworkCategory, getSellableSymbols ] );
};
