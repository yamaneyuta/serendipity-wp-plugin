import assert from 'node:assert';
import path from 'node:path';
import packageJson from '../package.json';
import { getPackagesComposer } from '@serendipity/export-license';

// package.json内でのlicense-checkerを実行するスクリプト名
const JS_LICENSE_CHECKER_SCRIPT_NAME = 'check-license:js';

/**
 * PHPのライセンスチェックを行います。
 * ※ 許可するライセンスは、`package.json`内、`check-license:js`コマンドの`--onlyAllow`で指定されたものを使用。
 */
const main = () => {
	// 許可されたライセンス一覧を取得
	const allowedLicenses = getAllowedLicenses();
	// 許可されたライセンスの互換性をチェック
	checkIncompatibilityGPLv2( allowedLicenses );

	// composerを使ってPHPの依存ライブラリを取得
	const projectPath = path.join( process.cwd(), 'includes' );
	const packages = getPackagesComposer( projectPath );

	// ライセンスチェック
	let hasError = false;
	for ( const p of Object.keys( packages ) ) {
		const licenses = packages[ p ].licenses;
		assert( typeof licenses === 'string', '[415E84D3] Invalid license' ); // 現時点でstring型のみ許可
		if ( ! allowedLicenses.includes( licenses ) ) {
			console.error( `[415E84D3] Invalid license - packageName: ${ p }, license: ${ licenses }` );
			hasError = true;
		}
	}
	if ( hasError ) {
		process.exit( 1 );
	}
};

/**
 * GPLv2との互換性をチェックします。
 * 互換が無いライセンスが許可するライセンス一覧に含まれている場合はエラー表示を行ってプロセスを終了します。
 * @param allowedLicenses
 */
const checkIncompatibilityGPLv2 = ( allowedLicenses: string[] ) => {
	// GPLv3の文字列が含まれているかどうか
	// `GPLv3`や`GPLv3 or later`は`GPLv2`と互換性がないため、エラーとする
	const isGPLv3 = ( license: string ) => {
		return license.toUpperCase().includes( 'GPL' ) && license.includes( '3' );
	};

	// Apache 2.0の文字列が含まれているかどうか
	// `Apache 2.0`は`GPLv2`と互換性がないため、エラーとする
	// ただし、`Apache-2.0 WITH LLVM-exception`は`GPLv2`と互換性があるため、エラーとしない
	const isApache2 = ( license: string ) => {
		if ( license === 'Apache-2.0 WITH LLVM-exception' ) {
			return false;
		}
		return license.toUpperCase().includes( 'APACHE' ) && license.includes( '2' );
	};

	// 互換が無いライセンスが許可リストに含まれている場合はエラーを表示して終了
	for ( const license of allowedLicenses ) {
		if ( isGPLv3( license ) || isApache2( license ) ) {
			console.error( `[0BE7A453] Invalid license - license: ${ license }` );
			process.exit( 1 );
		}
	}
};

/**
 * 許可されたライセンス一覧を取得します。
 */
const getAllowedLicenses = () => {
	const jsLicenseCheckCommand = packageJson.scripts[ JS_LICENSE_CHECKER_SCRIPT_NAME ];

	// `--onlyAllow`の値を取得
	const onlyAllow = jsLicenseCheckCommand.match( /--onlyAllow '(.+?)'/ )![ 1 ];
	// 許可するライセンスを取得
	const allowedLicenses = onlyAllow.split( ';' );
	assert( allowedLicenses.length > 0, '[49417B74] Invalid license-checker command' ); // ライセンスが指定されていない場合はエラー
	assert( ! allowedLicenses.includes( '' ), '[7DE2DC0E] Invalid license-checker command' ); // 空白文字のライセンスが含まれている場合はエラー

	return allowedLicenses;
};

main();
