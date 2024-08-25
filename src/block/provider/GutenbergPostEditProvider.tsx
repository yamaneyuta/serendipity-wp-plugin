import { QueryClient } from '@tanstack/react-query';
import { BlockEditorPropertyProvider } from './windowData/editor/BlockEditorPropertyProvider';
import { ServerDataProvider } from './serverData/ServerDataProvider';
import { PostIDProvider } from './windowData/postID/PostIDProvider';
import { WidgetStateProvider } from './widgetState/WidgetStateProvider';

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
		<>
			{ /* 投稿IDを取得 */ }
			<PostIDProvider>
				{ /* WordPressのエディタ情報を取得 */ }
				<BlockEditorPropertyProvider>
					{ /* サーバーに保存されている情報(投稿設定)を取得 */ }
					<ServerDataProvider client={ client }>
						{ /* ウィジェットの状態を保持 */ }
						<WidgetStateProvider>{ children }</WidgetStateProvider>
					</ServerDataProvider>
				</BlockEditorPropertyProvider>
			</PostIDProvider>
		</>
	);
};
