import { useMemo } from 'react';

export const usePostIDFromDom = () => {
	return useMemo( () => getPostIDFromDom(), [] );
};

/**
 * DOMから投稿IDを取得します。
 */
const getPostIDFromDom = () => {
	const postIDElement = document.getElementById( 'post_ID' );
	return postIDElement ? parseInt( ( postIDElement as HTMLInputElement ).value, 10 ) : null;
};
