import { getPackageManagerType } from './getPackageManagerType';

/**
 * サードパーティ製パッケージが格納されるディレクトリ名を取得します。
 * @param start
 */
export const getVendorDirName = ( start: string ) => {
	const { isNpm, isComposer } = getPackageManagerType( start );

	if ( isNpm ) {
		return 'node_modules';
	} else if ( isComposer ) {
		return 'vendor';
	}

	throw new Error( `[245BAAA9] No vendor directory: ${ start }` );
};
