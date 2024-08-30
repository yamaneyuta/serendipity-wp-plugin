import assert from 'assert';
import fs from 'fs';
import path from 'path';
import { ModuleInfo } from 'license-checker';

const META_FILE_NAME = 'license.json';

export const exportLicense = ( packages: ModuleInfo, vendorRootPath: string, exportDir: string ) => {
	assert( ! vendorRootPath.endsWith( '/' ), '[116748DA] vendorRootPath must not end with "/"' );

	// ライセンスファイルをコピー
	for ( const p of Object.keys( packages ) ) {
		const src = packages[ p ].licenseFile as string | undefined;
		if ( ! src ) {
			continue;
		}
		const dst = src.replace( vendorRootPath, exportDir );
		// ディレクトリが存在しない場合は作成
		const dir = path.dirname( dst );
		if ( ! fs.existsSync( dir ) ) {
			fs.mkdirSync( dir, { recursive: true } );
		}

		// ライセンスファイルをコピー
		require( 'fs' ).copyFileSync( src, dst );
		console.log( dst ); // eslint-disable-line no-console
	}

	// ライセンス情報をエクスポート
	const result = JSON.parse( ( JSON.stringify( packages ) as string ).replaceAll( vendorRootPath, '.' ) );
	const metaFilePath = path.join( exportDir, META_FILE_NAME );
	require( 'fs' ).writeFileSync( metaFilePath, JSON.stringify( result, null, 2 ) );
	console.log( metaFilePath ); // eslint-disable-line no-console
};
