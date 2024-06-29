import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { usePostSettingQuery } from '../../types/gql/generated';
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
	const postID = usePostIDFromDom();
	const { data: postSetting } = usePostSettingQuery( { postID: postID ?? 0 } );

	// console.log( data );
	// console.log( JSON.stringify( postSetting, null, 2 ) );

	return <div>GutenbergPostEdit</div>;
};
