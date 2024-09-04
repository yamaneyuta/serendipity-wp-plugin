import { useMemo } from 'react';
import { useGetSellableSymbolsCallback } from '../../provider/serverData/useGetSellableSymbolsCallback';
import { useSelectedNetwork } from '../../provider/widgetState/selectedNetwork/useSelectedNetwork';

/**
 * 画面で選択可能な通貨シンボル一覧を取得します。
 */
export const useSelectableSymbols = (): string[] | null | undefined => {
	// 画面で選択されているネットワーク
	const { selectedNetwork } = useSelectedNetwork();
	// ネットワークに応じた販売可能な通貨シンボル一覧を取得するコールバック
	const getSellableSymbols = useGetSellableSymbolsCallback();

	return useMemo( () => {
		if ( selectedNetwork === undefined ) {
			// 画面初期化中の場合
			return undefined;
		}
		if ( selectedNetwork === null ) {
			// 販売ネットワークが未指定の場合
			return null;
		}
		// 指定されたネットワークで販売可能な通貨シンボル一覧を取得
		// (販売可能な通貨シンボル一覧をAPIから取得できていない状態の場合はundefinedが返る)
		return getSellableSymbols( selectedNetwork );
	}, [ selectedNetwork, getSellableSymbols ] );
};
