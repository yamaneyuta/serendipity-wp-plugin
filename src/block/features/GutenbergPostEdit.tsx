import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useEchoQuery } from '../../types/gql/generated';

const client = new QueryClient();

export const GutenbergPostEdit: React.FC = () => {
	return (
		<QueryClientProvider client={ client }>
			<GutenbergPostEditApp />
		</QueryClientProvider>
	);
};
const GutenbergPostEditApp: React.FC = () => {
	const { data } = useEchoQuery(
		{
			endpoint: 'http://localhost:8888/index.php?rest_route=/todo-list/graphql',
		},
		{ message: 'hello typescript client' }
	);

	return <div>GutenbergPostEdit</div>;
};
