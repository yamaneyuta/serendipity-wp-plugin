import { useMemo } from 'react';

/**
 * PHPから出力されたJavaScript変数からREST API関連の情報を取得します。
 */
export const useRestPhpVar = () => {
	return useMemo( () => {
		return getRestPhpVar();
	}, [] );
};

type RestPhpVar = {
	wpRestNonce: string;
	graphqlUrl: string;
};

const getRestPhpVar = (): RestPhpVar | null => {
	// TODO: jsonから取得
	const varName = 'php_var_rest_20792bdd';
	return ( window as any )[ varName ] ?? null;
};
