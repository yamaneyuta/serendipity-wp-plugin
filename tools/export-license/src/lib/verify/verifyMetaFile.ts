import assert from 'node:assert';
import fs from 'node:fs';
import path from 'node:path';
import { ModuleInfos } from 'license-checker';

/**
 * ライセンス情報が記載されたメタファイルを検証します。
 * @param metaFile
 */
export const verifyMetaFile = ( metaFile: string ) => {
	// ファイルが存在しない場合はエラー
	checkMetaFileExist( metaFile );

	// メタファイルの内容に記載のライセンスのパスにファイルが存在しない場合はエラー
	checkLicenseFileExist( metaFile );
};

/**
 * メタファイルが存在するか検証し、存在しない場合はエラーをスローします。
 * @param metaFile
 */
const checkMetaFileExist = ( metaFile: string ) => {
	if ( ! fs.statSync( metaFile ).isFile() ) {
		throw new Error( '[726211C4] metaFile is not found.' );
	}
};

/**
 * メタファイルに記載されたライセンスファイルが存在しない場合はエラーをスローします。
 * @param metaFile
 */
const checkLicenseFileExist = ( metaFile: string ) => {
	// メタファイルを読み込む
	const meta = JSON.parse( fs.readFileSync( metaFile, 'utf-8' ) ) as ModuleInfos;

	for ( const key in meta ) {
		const licenseFile = meta[ key ].licenseFile;
		assert( licenseFile, '[00955602] licenseFile is not found. key: ' + key );
		const licensePath = path.join( path.dirname( metaFile ), licenseFile );
		console.log( 'licensePath: ', licensePath );
		// ライセンスファイルが存在しない場合はエラー
		if ( ! fs.existsSync( licensePath ) ) {
			throw new Error( `[E5A2D1A3] licenseFile is not found. licenseFile: ${ licensePath }` );
		}
	}
};
