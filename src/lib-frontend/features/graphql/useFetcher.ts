import { useGraphQLUrl } from '../global/useGraphQLUrl';
import { useWpRestNonce } from '../global/useWpRestNonce';

export const useFetcher = < TData, TVariables >( query: string, variables?: TVariables ) => {
	const { endpoint, requestInit } = useFetchParames();

	return fetcher< TData, TVariables >( endpoint, requestInit, query, variables );
};

/**
 * graphql-condegenが生成したオリジナルのfetcher
 * ※ codegen.tsのconfigをコメントアウトして実行すると、この関数が生成される
 * @param endpoint
 * @param requestInit
 * @param query
 * @param variables
 */
function fetcher< TData, TVariables >(
	endpoint: string,
	requestInit: RequestInit,
	query: string,
	variables?: TVariables
) {
	return async (): Promise< TData > => {
		const res = await fetch( endpoint, {
			method: 'POST',
			...requestInit,
			body: JSON.stringify( { query, variables } ),
		} );

		const json = await res.json();

		if ( json.errors ) {
			const { message } = json.errors[ 0 ];

			throw new Error( message );
		}

		return json.data;
	};
}

const useFetchParames = () => {
	const endpoint = useGraphQLUrl();
	const nonce = useWpRestNonce();

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[11D62E9A] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	return {
		endpoint,
		requestInit: {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
		} as RequestInit,
	};
};
