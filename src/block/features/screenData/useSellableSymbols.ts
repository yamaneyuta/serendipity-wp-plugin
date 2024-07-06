import { useMemo } from 'react';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';

export const useSellableSymbols = (): string[] | undefined => {
	const postSetting = usePostSetting();

	return useMemo( () => {
		if ( postSetting === undefined ) {
			return undefined;
		}
		return postSetting.sellableSymbols;
	}, [ postSetting ] );
};
