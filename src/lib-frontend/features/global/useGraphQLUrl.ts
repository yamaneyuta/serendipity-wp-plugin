import { useRestPhpVar } from './_useRestPhpVar';

/**
 * GraphQLのURLを取得します。
 */
export const useGraphQLUrl = () => {
	const restPhpVar = useRestPhpVar();
	return restPhpVar !== null ? restPhpVar.graphqlUrl : null;
};
