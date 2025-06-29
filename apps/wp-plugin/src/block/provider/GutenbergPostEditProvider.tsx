import { QueryClient } from '@tanstack/react-query';
import { WindowDataProvider } from './windowData/WindowDataProvider';
import { WidgetStateProvider } from './widgetState/WidgetStateProvider';
import { ServerDataProvider } from './serverData/ServerDataProvider';
import { WidgetAttributes } from '../types/WidgetAttributes';

// アクティブになったときは再読みしない
const client = new QueryClient( {
	defaultOptions: {
		queries: {
			staleTime: Infinity,
		},
	},
} );

type GutenbergPostEditProviderProps = {
	attributes: Readonly< WidgetAttributes >;
	setAttributes: ( attrs: Partial< WidgetAttributes > ) => void;
	children: React.ReactNode;
};

export const GutenbergPostEditProvider: React.FC< GutenbergPostEditProviderProps > = ( {
	attributes,
	setAttributes,
	children,
} ) => {
	return (
		<>
			{ /* グローバルオブジェクトから取得した情報を保持 */ }
			<WindowDataProvider>
				{ /* サーバーに保存されている情報(投稿設定)を取得 */ }
				<ServerDataProvider client={ client }>
					{ /* ウィジェットの状態を保持 */ }
					<WidgetStateProvider attributes={ attributes } setAttributes={ setAttributes }>
						{ children }
					</WidgetStateProvider>
				</ServerDataProvider>
			</WindowDataProvider>
		</>
	);
};
