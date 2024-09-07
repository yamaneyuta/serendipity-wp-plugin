import assert from 'assert';
import { useCallback } from 'react';
import { usePostSetting } from '../../provider/serverData/postSetting/usePostSetting';
import { NetworkCategory } from '../../../types/NetworkCategory';

/**
 * 指定されたネットワークで販売可能な通貨シンボル一覧を取得するコールバックを返します。
 */
export const useGetSellableSymbolsCallback = () => {
	const postSetting = usePostSetting(); // サーバーから設定を取得

	return useCallback(
		( networkCategory: NetworkCategory ) => {
			if ( postSetting === undefined ) {
				// 読み込み中
				return undefined;
			}

			const selectableSymbols = ( () => {
				if ( networkCategory === NetworkCategory.mainnet() ) {
					return postSetting.mainnetSellableSymbols;
				}
				if ( networkCategory === NetworkCategory.testnet() ) {
					return postSetting.testnetSellableSymbols;
				}
				if ( networkCategory === NetworkCategory.privatenet() ) {
					return postSetting.privatenetSellableSymbols;
				}
				assert( false, `[3D102039] Invalid selling network type. - networkCategory: ${ networkCategory }` );
			} )();

			// APIの仕様上、selectableSymbolsはundefinedにはならない
			assert(
				selectableSymbols !== undefined,
				`[519DA805] Sellable symbols is undefined. - networkCategory: ${ networkCategory }`
			);

			return selectableSymbols;
		},
		[ postSetting ]
	);
};
