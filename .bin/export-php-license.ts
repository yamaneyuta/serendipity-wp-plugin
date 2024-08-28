import assert from 'node:assert/strict';
import path from 'node:path';
import fs from 'node:fs';
import glob from 'glob'; // eslint-disable-line import/no-extraneous-dependencies
import { exportLicense } from './export-license';

// 検索するライセンスファイルの名前(順番に検索)
const LICENSE_FILE_NAMES = [ 'LICENSE' ];

const EXPORT_DIR = path.join( process.cwd(), 'public', 'license', 'php' );

/**
 * プラグインが依存しているPHPライブラリのライセンスを出力します。
 */
const main = () => {
	const packages = getPackagesLikeLicenseChecker();
	exportLicense( packages, `${ process.cwd() }/includes/vendor`, EXPORT_DIR );
};

/**
 * npmの`license-checker`のような出力形式でライセンス情報を取得します。
 *
 * ※ `composer licenses --format=json`単体では、ライセンスファイルのパスが取得できなかったため、独自で抽出する処理を実装。
 * ※ また、https://github.com/Comcast/php-legal-licenses も同様にライセンスのパスを取得できそうになかった。
 */
const getPackagesLikeLicenseChecker = () => {
	// composerコマンドで出力したライセンス情報を取得
	const composerOutput = getComposerOutputDependencies();
	// composer.lockファイルの内容を取得
	const composerLock = getComposerLock();

	const packages = {};
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
		const licenseFile = getLicensePath( packageName );

		// リポジトリURLを取得
		const repository = getRepositoryUrl( packageName, composerLock );

		// console.log(`${packageName}@${version} ${licenses} ${licenseFile}`);
		packages[ `${ packageName }@${ version }` ] = {
			licenses,
			repository,
			licenseFile,
		};
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
 * @param packageName
 */
const getLicensePath = ( packageName: string ) => {
	// globパッケージを使用して、ライセンスファイルを検索
	for ( const licenseFileName of LICENSE_FILE_NAMES ) {
		const files = glob.sync( `/workspaces/includes/vendor/${ packageName }/**/${ licenseFileName }` );
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
 */
const getComposerLock = () => {
	// composer.lockファイルをJSONで読み込む
	return JSON.parse(
		fs.readFileSync( path.join( process.cwd(), 'includes', 'composer.lock' ) ).toString()
	) as ComposerLock;
};

/**
 * composerコマンドで出力した依存パッケージの情報を取得します。
 */
const getComposerOutputDependencies = () => {
	const cwd = process.cwd(); // eslint-disable-line @wordpress/no-unused-vars-before-return
	try {
		// カレントディレクトリを`includes`に変更
		process.chdir( 'includes' );

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

main();
