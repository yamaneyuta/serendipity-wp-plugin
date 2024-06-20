import { useRestPhpVar } from './_useRestPhpVar';

/**
 * REST APIにアクセスする際のNonceを取得します。
 */
export const useWpRestNonce = () => {
	const restPhpVar = useRestPhpVar();
	return restPhpVar !== null ? restPhpVar.wpRestNonce : null;
};
