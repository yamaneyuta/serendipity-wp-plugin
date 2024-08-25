import assert from 'assert';
import { useContext } from 'react';
import { SelectedNetworkContext } from './SelectedNetworkProvider';

/**
 * ユーザーが選択したネットワークを取得または設定する機能を提供します。
 */
export const useSelectedNetwork = () => {
	const context = useContext( SelectedNetworkContext );
	assert( context, '[90D2588E] Context is not found' );

	return context;
};
