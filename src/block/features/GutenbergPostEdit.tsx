import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { usePostSettingQuery, useSetPostSettingMutation } from '../../types/gql/generated';
import { usePostIDFromDom } from '../lib/postID/usePostIDFromDom';

// アクティブになったときは再読みしない
const client = new QueryClient( {
	defaultOptions: {
		queries: {
			staleTime: Infinity,
		},
	},
} );

export const GutenbergPostEdit: React.FC = () => {
	return (
		<QueryClientProvider client={ client }>
			<GutenbergPostEditApp />
		</QueryClientProvider>
	);
};
const GutenbergPostEditApp: React.FC = () => {
	const postID = usePostIDFromDom() ?? 0;
	const { data, refetch } = usePostSettingQuery( { postID } );
	const { mutateAsync } = useSetPostSettingMutation( {
		onSuccess: async () => {
			await refetch();
		},
	} );

	const onClick = async () => {
		await mutateAsync( {
			postID,
			postSetting: {
				sellingPrice: {
					// ダミーデータ
					amountHex: '0x1234567890abcdef',
					decimals: 18,
					symbol: 'USDT',
				},
			},
		} );
	};

	return (
		<>
			<div>GutenbergPostEdit</div>

			<div>
				<button onClick={ onClick }>hoge</button>
			</div>

			<div>amount: { JSON.stringify( data?.postSetting ) }</div>
		</>
	);
};
