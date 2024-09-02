import fs from 'node:fs';
import path from 'node:path';
import { ModuleInfos } from 'license-checker';
import { getVendorDirName } from './getVendorDirName';

/**
 * ライセンスファイルをコピーします。
 * @param packages
 * @param start
 * @param output
 */
export const copyLicenseFiles = ( packages: ModuleInfos, start: string, output: string ) => {
	for ( const name of Object.keys( packages ) ) {
		const src = packages[ name ].licenseFile;
		if ( ! src ) {
			console.warn( `[0E2ACD6A] No license file: ${ name }` );
			continue;
		}

		// コピー先ライセンスファイルのパスを取得
		const dst = src.replace( path.join( start, getVendorDirName( start ) ), output );
		// ディレクトリが存在しない場合は作成
		const dir = path.dirname( dst );
		if ( ! fs.existsSync( dir ) ) {
			fs.mkdirSync( dir, { recursive: true } );
		}

		// ライセンスファイルをコピー
		if ( ! fs.existsSync( dst ) ) {
			fs.copyFileSync( src, dst );
			console.log( dst );
		}
	}
};
