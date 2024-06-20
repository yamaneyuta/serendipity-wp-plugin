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
	const { data } = useEchoQuery( { message: 'hello typescript client' } );

	console.warn( data );

	return <div>GutenbergPostEdit</div>;
};
