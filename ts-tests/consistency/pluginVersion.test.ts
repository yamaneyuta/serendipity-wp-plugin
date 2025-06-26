import { expect } from '@jest/globals';
import { EntryPhpFile } from '../lib/EntryPhpFile';

/**
 * プラグインバージョンの整合性チェック
 * PHPのヘッダに記載されているバージョンとpackage.jsonのバージョンが一致していることを確認する。
 */
it( '[3160CFA4] Plugin version consistency check', async () => {
	// ARRANGE
	// package.jsonからバージョンを取得
	const packageJsonVersion = require( process.cwd() + '/package.json' ).version;
	// エントリファイル(PHP)のヘッダからプラグインのバージョンを取得
	const phpHeaderVersion = EntryPhpFile.getVersion();

	// ACT
	// Do nothing

	// ASSERT
	// package.jsonのバージョンとPHPファイルのバージョンが一致していることを確認
	expect( packageJsonVersion ).toEqual( phpHeaderVersion );
} );
