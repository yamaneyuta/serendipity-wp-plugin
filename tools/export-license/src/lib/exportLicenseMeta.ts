import fs from 'node:fs';
import path from 'node:path';
import { ModuleInfos } from 'license-checker';
import { getVendorDirName } from './getVendorDirName';

export const exportLicenseMeta = async ( packages: ModuleInfos, start: string, output: string, metaFile: string ) => {
	// metaFileから見たoutputの相対パスを取得
	const metaFileRelative = path.relative( path.dirname( metaFile ), output ) || '.';

	// パッケージが格納されているディレクトリのパスを取得
	const vendorRoot = path.join( start, getVendorDirName( start ) );

	// packages内のパスをmetaFileからの相対パスに変換
	const result = JSON.parse( JSON.stringify( packages ).replaceAll( vendorRoot, metaFileRelative ) );

	// metaFileのディレクトリが存在しない場合は作成
	const dir = path.dirname( metaFile );
	if ( ! fs.existsSync( dir ) ) {
		fs.mkdirSync( dir, { recursive: true } );
	}

	// ライセンス情報を出力
	fs.writeFileSync( metaFile, JSON.stringify( result, null, 2 ) );
};
