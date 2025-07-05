import assert from 'node:assert/strict';
import path from 'node:path';
import fs from 'node:fs';
import { globSync } from 'glob';
import { ModuleInfos, ModuleInfo } from 'license-checker';

// 検索するライセンスファイルの名前(順番に検索)
const LICENSE_FILE_NAMES = [ 'LICENSE', 'LICENSE.md' ];

/**
 * composerで管理されているパッケージのライセンス情報を取得します。
 * @param projectPath
 */
export const getPackagesComposer = ( projectPath: string ) => {
	// composerコマンドで出力したライセンス情報を取得
	const composerOutput = getComposerOutputDependencies( projectPath );
	// composer.lockファイルの内容を取得
	const composerLock = getComposerLock( projectPath );

	const packages = {} as ModuleInfos;
	for ( const packageName of Object.keys( composerOutput ) ) {
		// バージョンを取得(例: "v1.0.0" => "1.0.0")
		const version = composerOutput[ packageName ].version.replace( /^v/, '' );
		// ライセンスを文字列で取得
		const licenses = ( () => {
			// ライセンスを2つ以上指定する場合は`(MIT AND CC-BY-4.0)`のように記述する。
			// 参考: https://qiita.com/0x50/items/cb9fb821a9ff4c46269a
			const licenseArray = composerOutput[ packageName ].license;
			const licenseText = licenseArray.join( ' AND ' );
			return licenseArray.length > 1 ? `(${ licenseText })` : licenseText;
		} )();
		// ライセンスファイルのパスを取得
		const licenseFile = getLicensePath( projectPath, packageName );

		// リポジトリURLを取得
		const repository = getRepositoryUrl( packageName, composerLock );

		// console.log(`${packageName}@${version} ${licenses} ${licenseFile}`);
		packages[ `${ packageName }@${ version }` ] = {
			licenses,
			repository,
			licenseFile,
		} as ModuleInfo;
	}

	return packages;
};

/**
 * composer.lockファイルの構造
 */
type ComposerLock = {
	packages: {
		name: string;
		version: string;
		source: {
			type: 'git';
			url: string;
			reference: string;
		};
		authors:
			| {
					name: string;
					email: string;
			  }[]
			| undefined;
	}[];
};

/**
 * 指定したパッケージのライセンスファイルパスを取得します。
 * @param projectPath
 * @param packageName
 */
const getLicensePath = ( projectPath: string, packageName: string ) => {
	const vendorRootPath = path.join( projectPath, 'vendor' );
	// globパッケージを使用して、ライセンスファイルを検索
	for ( const licenseFileName of LICENSE_FILE_NAMES ) {
		const files = globSync( `${ vendorRootPath }/${ packageName }/**/${ licenseFileName }` );
		if ( files.length > 0 ) {
			return files[ 0 ];
		}
	}

	assert( false, `[949F06CD] Not found license file path - packageName: ${ packageName }` );
};

/**
 * パッケージのリポジトリURLを取得します。
 * @param packageName
 * @param composerLock
 */
const getRepositoryUrl = ( packageName: string, composerLock: ComposerLock ) => {
	const packageInfo = composerLock.packages.find( ( p ) => p.name === packageName );
	if ( packageInfo === undefined ) {
		assert( false, `[28334FB5] Not found package info - packageName: ${ packageName }` );
		// return undefined;
	}
	return packageInfo.source.url;
};

/**
 * composer.lockファイルの内容を取得します。
 * @param projectPath
 */
const getComposerLock = ( projectPath: string ) => {
	// composer.lockファイルをJSONで読み込む
	return JSON.parse( fs.readFileSync( path.join( projectPath, 'composer.lock' ) ).toString() ) as ComposerLock;
};

/**
 * composerコマンドで出力した依存パッケージの情報を取得します。
 * @param projectPath
 */
const getComposerOutputDependencies = ( projectPath: string ) => {
	const cwd = process.cwd(); // eslint-disable-line @wordpress/no-unused-vars-before-return
	try {
		// カレントディレクトリをプロジェクトディレクトリ(composer.jsonが存在するディレクトリ)に変更
		process.chdir( projectPath );

		// `composer`を使用してライセンス一覧を取得
		const execSync = require( 'child_process' ).execSync;
		const output = JSON.parse( execSync( 'composer licenses --no-dev --format=json' ).toString() );
		return output.dependencies as {
			[ packageName: string ]: {
				version: string;
				license: string[];
			};
		};
	} finally {
		// カレントディレクトリを元に戻す
		process.chdir( cwd );
	}
};
