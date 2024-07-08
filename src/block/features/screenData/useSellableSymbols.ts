import { useMemo } from 'react';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';
import { NetworkType } from '../../../types/gql/generated';

export const useSellableSymbols = ( sellingNetwork: NetworkType | null | undefined ): string[] | null | undefined => {
	const postSetting = usePostSetting();

	return useMemo( () => {
		if ( sellingNetwork === null ) {
			return null;
		}
		if ( sellingNetwork === undefined || postSetting === undefined ) {
			return undefined;
		}

		switch ( sellingNetwork ) {
			case NetworkType.Mainnet:
				return postSetting.mainnetSellableSymbols;
			case NetworkType.Testnet:
				return postSetting.testnetSellableSymbols;
			case NetworkType.Privatenet:
				return postSetting.privatenetSellableSymbols;
			default:
				throw new Error( 'Invalid selling network type - sellingNetwork: ' + sellingNetwork );
		}
	}, [ sellingNetwork, postSetting ] );
};
