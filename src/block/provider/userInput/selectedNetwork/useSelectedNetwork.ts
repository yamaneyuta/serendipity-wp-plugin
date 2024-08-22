import { useContext } from 'react';
import { SelectedNetworkContext } from './SelectedNetworkProvider';

/**
 * ユーザーが選択したネットワークを取得または設定します。
 */
export const useSelectedNetwork = () => {
	const context = useContext( SelectedNetworkContext );
	if ( ! context ) {
		throw new Error( '[90D2588E] Context is not found' );
	}

	return context;
};
