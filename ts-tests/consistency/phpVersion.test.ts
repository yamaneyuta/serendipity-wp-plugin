import fs from 'node:fs';
import path from 'node:path';
import { expect } from '@jest/globals';
import { load } from 'js-yaml';
import { EntryPhpFile } from '../lib/EntryPhpFile';

/**
 * エントリポイントとなるPHPに記載の最低限必要なPHPバージョンがCIの設定に含まれていることを確認する
 */
it( '[975682DD] WordPress Requires at latest version is tested', async () => {
	// エントリファイルとなるPHPから最低限必要なWordPressバージョンを取得
	const requiresPHP = EntryPhpFile.getRequiresPHP();

	// GitHub Actionsの設定ファイルからテストを実行しているWordPressのバージョン一覧を取得
	const workflow = load( fs.readFileSync( path.resolve( process.cwd(), '.github/workflows/ci.yml' ), 'utf-8' ) );
	const matrix: { 'php-version': string }[] = ( workflow as any ).jobs.ci.strategy.matrix.env;
	const versions = matrix.map( ( v ) => v[ 'php-version' ] );
	const oldestVersion = versions[ 0 ];

	// 取得したテスト済みPHPのバージョンの一番古いものが、PHPに記載されている最低限必要なバージョンと一致していることを確認
	expect( oldestVersion ).toBe( requiresPHP );
} );
