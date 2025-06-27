import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { PostSettingProvider } from './postSetting/PostSettingProvider';

type ServerDataProviderProps = {
	client: QueryClient;
	children: React.ReactNode;
};
export const ServerDataProvider: React.FC< ServerDataProviderProps > = ( { client, children } ) => {
	return (
		<QueryClientProvider client={ client }>
			<PostSettingProvider>{ children }</PostSettingProvider>
		</QueryClientProvider>
	);
};
