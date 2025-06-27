import assert from 'assert';
import { createContext } from 'react';
import { usePostIDFromDom } from '../../../lib/postID/usePostIDFromDom';

type PostIdType = ReturnType< typeof _usePostID >;

export const PostIDContext = createContext< PostIdType | undefined >( undefined );

const _usePostID = (): number => {
	const postID = usePostIDFromDom();

	// 投稿編集画面ではpostIDが取得できる
	assert( postID !== null, '[50F2A586] postID is null' );

	return postID;
};

type PostIDProviderProps = {
	children: React.ReactNode;
};

export const PostIDProvider: React.FC< PostIDProviderProps > = ( { children } ) => {
	const value = _usePostID();
	return <PostIDContext.Provider value={ value }>{ children }</PostIDContext.Provider>;
};
