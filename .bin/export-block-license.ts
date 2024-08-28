import path from 'path';
import fs from 'fs';
import * as checker from 'license-checker';

const EXPORT_DIR = path.join( process.cwd(), 'public', 'license', 'block' );
const META_FILE_PATH = path.join( EXPORT_DIR, 'license.json' );

checker.init(
	{
		start: process.cwd(),
		excludePrivatePackages: true, // プライベートパッケージは除外
		production: true, // devDependencies は除外
	},
	( err, packages ) => {
		// ライセンスファイルをコピー
		for ( const p of Object.keys( packages ) ) {
			const src = packages[ p ].licenseFile as string | undefined;
			if ( ! src ) {
				continue;
			}
			const dst = src.replace( `${ process.cwd() }/node_modules`, EXPORT_DIR );
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
		const result = JSON.parse(
			( JSON.stringify( packages ) as string ).replaceAll( `${ process.cwd() }/node_modules`, '.' )
		);
		require( 'fs' ).writeFileSync( META_FILE_PATH, JSON.stringify( result, null, 2 ) );
		console.log( META_FILE_PATH ); // eslint-disable-line no-console
	}
);
