import { PostIDProvider } from './postID/PostIDProvider';

type WindowDataProviderProps = {
	children: React.ReactNode;
};

export const WindowDataProvider: React.FC< WindowDataProviderProps > = ( { children } ) => {
	return <PostIDProvider>{ children }</PostIDProvider>;
};
