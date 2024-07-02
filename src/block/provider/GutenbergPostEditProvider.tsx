import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { BlockEditorPropertyProvider } from './editor/BlockEditorPropertyProvider';
import { PostSettingProvider } from './postSetting/PostSettingProvider';
import { PostIDProvider } from './postID/PostIDProvider';

// アクティブになったときは再読みしない
const client = new QueryClient( {
	defaultOptions: {
		queries: {
			staleTime: Infinity,
		},
	},
} );

type GutenbergPostEditProviderProps = {
	children: React.ReactNode;
};

export const GutenbergPostEditProvider: React.FC< GutenbergPostEditProviderProps > = ( { children } ) => {
	return (
		<QueryClientProvider client={ client }>
			{ /* 投稿IDを取得 */ }
			<PostIDProvider>
				{ /* WordPressのエディタ情報を取得 */ }
				<BlockEditorPropertyProvider>
					{ /* 投稿設定情報を取得 */ }
					<PostSettingProvider>{ children }</PostSettingProvider>
				</BlockEditorPropertyProvider>
			</PostIDProvider>
		</QueryClientProvider>
	);
};
