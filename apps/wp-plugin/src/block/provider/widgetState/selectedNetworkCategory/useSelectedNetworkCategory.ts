import assert from 'assert';
import { useContext } from 'react';
import { SelectedNetworkCategoryContext } from './SelectedNetworkCategoryProvider';

/**
 * ユーザーが選択したネットワークカテゴリを取得または設定する機能を提供します。
 */
export const useSelectedNetworkCategory = () => {
	const context = useContext( SelectedNetworkCategoryContext );
	assert( context, '[90D2588E] Context is not found' );

	return context;
};
