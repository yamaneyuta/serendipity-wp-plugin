import assert from 'node:assert/strict';
import * as checker from 'license-checker';

/**
 * npmで管理されているパッケージのライセンス情報を取得します。
 * @param opts
 */
export const getPackagesNpm = async ( opts: checker.InitOpts ) => {
	const result: checker.ModuleInfos = await new Promise( ( resolve, reject ) => {
		checker.init( opts, ( err, packages ) => {
			if ( err ) {
				reject( err );
			} else {
				resolve( packages );
			}
		} );
	} );
	assert( result, '[CEF76120] `checker.init` failed' );

	return result;
};
