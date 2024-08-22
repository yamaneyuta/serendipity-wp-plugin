import { useCallback } from 'react';
import { NetworkType } from '../../../types/gql/generated';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';

/**
 * 指定されたネットワークで販売可能な通貨シンボル一覧を取得するコールバックを返します。
 * @param network
 */
export const useGetSellableSymbolsCallback = ( network: NetworkType ) => {
	const postSetting = usePostSetting(); // サーバーから設定を取得

	return useCallback( () => {
		if ( postSetting === undefined ) {
			// 読み込み中
			return undefined;
		}

		const selectableSymbols = ( () => {
			switch ( network ) {
				case NetworkType.Mainnet:
					return postSetting.mainnetSellableSymbols;
				case NetworkType.Testnet:
					return postSetting.testnetSellableSymbols;
				case NetworkType.Privatenet:
					return postSetting.privatenetSellableSymbols;
				default:
					throw new Error( '[3D102039] Invalid selling network type. - network: ' + network );
			}
		} )();

		// APIの仕様上、selectableSymbolsはundefinedにはならない
		if ( selectableSymbols === undefined ) {
			throw new Error( '[519DA805] Sellable symbols is undefined. - network: ' + network );
		}

		return selectableSymbols;
	}, [ network, postSetting ] );
};
