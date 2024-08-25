import { QueryClient } from '@tanstack/react-query';
import { WindowDataProvider } from './windowData/WindowDataProvider';
import { WidgetStateProvider } from './widgetState/WidgetStateProvider';
import { ServerDataProvider } from './serverData/ServerDataProvider';

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
			{ /* グローバルオブジェクトから取得した情報を保持 */ }
			<WindowDataProvider>
				{ /* サーバーに保存されている情報(投稿設定)を取得 */ }
				<ServerDataProvider client={ client }>
					{ /* ウィジェットの状態を保持 */ }
					<WidgetStateProvider>{ children }</WidgetStateProvider>
				</ServerDataProvider>
			</WindowDataProvider>
		</>
	);
};
