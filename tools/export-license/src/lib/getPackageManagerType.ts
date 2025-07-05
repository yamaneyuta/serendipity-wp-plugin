import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

export type PackageManagerType = { isNpm: boolean; isComposer: boolean };

export const getPackageManagerType = ( start: string ): PackageManagerType => {
	// 本プロジェクトでは、`composer.lock`と`package.json`の両方が同一ディレクトリに存在する状態にならないため、
	// ここではディレクトリ指定のみ有効とする。
	if ( ! fs.statSync( start ).isDirectory() ) {
		throw new Error( `[4BBE66A8] Invalid argument. start: ${ start }` );
	}

	const result: PackageManagerType = {
		isNpm: fs.existsSync( path.join( start, 'package.json' ) ),
		isComposer: fs.existsSync( path.join( start, 'composer.lock' ) ),
	};
	assert(
		Object.values( result ).filter( ( v ) => v ).length === 1,
		`[D4A3B7E7] Invalid argument. start: ${ start }`
	);

	return result;
};
