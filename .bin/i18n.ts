import fs from 'node:fs';
import path from 'node:path';
import spawn from 'node:child_process';
import packageJson from '../package.json';

/** テキストドメイン */
const TEXT_DOMAIN = packageJson.name;

/** プラグインが格納されているディレクトリ(wpI18nコマンド使用時) */
const WP_CLI_PLUGIN_DIR: string = path.join( 'wp-content/plugins', path.basename( process.cwd() ) );

/** 翻訳ファイルが一時的に格納されるディレクトリ */
const I18N_WORK_DIR_NAME = 'i18n/wp-plugin';

/** プラグインに含めるファイルを格納するディレクトリ名 */
const OUTPUT_DIR_NAME = 'languages';

/** 翻訳対象とする言語一覧(enを除く) */
const LANGUAGES = [ 'ja' ];

/**
 * .potファイルを生成します。
 */
const makePot = () => {
	wpI18n( [
		'make-pot',
		WP_CLI_PLUGIN_DIR,
		path.join( WP_CLI_PLUGIN_DIR, I18N_WORK_DIR_NAME, `${ TEXT_DOMAIN }.pot` ),
		'--domain=' + TEXT_DOMAIN,
		'--include=includes/classes',
	] );
};

/**
 * .poファイルが存在しない場合、.potファイルをコピーして.poファイルを作成します。
 */
const makePoIfNotExists = () => {
	for ( const lang of LANGUAGES ) {
		const poFilePath = path.join( I18N_WORK_DIR_NAME, `${ TEXT_DOMAIN }-${ lang }.po` );
		if ( ! fs.existsSync( poFilePath ) ) {
			fs.copyFileSync( path.join( I18N_WORK_DIR_NAME, `${ TEXT_DOMAIN }.pot` ), poFilePath );
		}
	}
};

/**
 * .poファイルを更新します。
 */
const updatePo = () => {
	for ( const lang of LANGUAGES ) {
		wpI18n( [
			'update-po',
			path.join( WP_CLI_PLUGIN_DIR, I18N_WORK_DIR_NAME, `${ TEXT_DOMAIN }.pot` ),
			path.join( WP_CLI_PLUGIN_DIR, I18N_WORK_DIR_NAME, `${ TEXT_DOMAIN }-${ lang }.po` ),
		] );
	}
};

/**
 * .moファイルを生成します。
 */
const makeMo = () => {
	for ( const lang of LANGUAGES ) {
		wpI18n( [
			'make-mo',
			path.join( WP_CLI_PLUGIN_DIR, I18N_WORK_DIR_NAME, `${ TEXT_DOMAIN }-${ lang }.po` ),
			path.join( WP_CLI_PLUGIN_DIR, OUTPUT_DIR_NAME ),
		] );
	}
};

/**
 * wp-env run cli wp 18n コマンドを実行します。
 * @param args wp i18n 以降の引数
 */
const wpI18n = async ( args: string[] ) => {
	const childProcess = spawn.spawnSync( 'npx', [ 'wp-env', 'run', 'cli', 'wp', 'i18n', ...args ], {
		stdio: 'inherit',
	} );
	// エラーが発生した場合
	if ( childProcess.error ) {
		console.error( childProcess.error );
		process.exit( childProcess.status ?? 1 );
	}
};

const main = async () => {
	makePot();
	makePoIfNotExists();
	updatePo();
	makeMo();
};
( async () => {
	await main();
} )();
