import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useEchoQuery, usePostSellingInfoQuery } from '../../types/gql/generated';
import { usePostIDFromDom } from '../lib/postID/usePostIDFromDom';

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
	const postID = usePostIDFromDom();
	const { data: postSellingInfo } = usePostSellingInfoQuery( { postID: postID ?? 0 } );

	// console.log( data );
	// console.log( JSON.stringify( postSellingInfo, null, 2 ) );

	return <div>GutenbergPostEdit</div>;
};
