import fs from 'node:fs';
import path from 'node:path';
import { load } from 'js-yaml';
import { EntryPhpFile } from '../lib/EntryPhpFile';
import { ReadmeTxt } from '../lib/ReadmeTxt';

/**
 * プラグインバージョンの整合性チェック
 * PHPに記載の最低限必要なWordPressバージョンがCIの設定に含まれていることを確認する。
 */
it( '[975682DD] WordPress Requires at latest version is tested', async () => {
	// エントリファイルとなるPHPから最低限必要なWordPressバージョンを取得
	const requiresAtLeast = EntryPhpFile.getRequiresAtLeast();

	// GitHub Actionsの設定ファイルからテストを実行しているWordPressのバージョン一覧を取得
	const workflow = load( fs.readFileSync( path.resolve( process.cwd(), '.github/workflows/ci.yml' ), 'utf-8' ) );
	const matrix: { 'wordpress-version': string }[] = ( workflow as any ).jobs.ci.strategy.matrix.env;
	const versions = matrix.map( ( v ) => v[ 'wordpress-version' ] );
	const oldestVersion = versions[ 0 ];

	// 取得したテスト済みWordPressのバージョンの一番古いものが、PHPに記載されている最低限必要なバージョンと一致していることを確認
	expect( oldestVersion ).toBe( requiresAtLeast );
} );

/**
 * PHPに記載の最低限必要なWordPressバージョンとreadme.txtに記載の最低限必要なWordPressバージョンが一致していることを確認する。
 */
it( '[0491C5A3] WordPress Requires at latest version is tested', async () => {
	// エントリファイルとなるPHPから最低限必要なWordPressバージョンを取得
	const phpRequiresAtLeast = EntryPhpFile.getRequiresAtLeast();

	// readme.txtから最低限必要なWordPressバージョンを取得
	const readmeRequiresAtLeast = ReadmeTxt.getRequiresAtLeast();

	// 両方のバージョンが一致していることを確認
	expect( readmeRequiresAtLeast ).toBe( phpRequiresAtLeast );
} );
