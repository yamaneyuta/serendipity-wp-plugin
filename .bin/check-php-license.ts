import assert from 'node:assert';
import path from 'node:path';
import packageJson from '../package.json';
import { getComposerPackages } from './lib/getComposerPackages';

// package.json内でのlicense-checkerを実行するスクリプト名
const JS_LICENSE_CHECKER_SCRIPT_NAME = 'check-license:js';

/**
 * PHPのライセンスチェックを行います。
 * ※ 許可するライセンスは、`package.json`内、`check-license:js`コマンドの`--onlyAllow`で指定されたものを使用。
 */
const main = () => {
	// 許可されたライセンス一覧を取得
	const allowedLicenses = getAllowedLicenses();

	// composerを使ってPHPの依存ライブラリを取得
	const projectPath = path.join( process.cwd(), 'includes' );
	const packages = getComposerPackages( projectPath );

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
