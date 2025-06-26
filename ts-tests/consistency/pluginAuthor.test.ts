import { expect } from '@jest/globals';
import { EntryPhpFile } from '../lib/EntryPhpFile';
import { ReadmeTxt } from '../lib/ReadmeTxt';

/**
 * プラグイン作者の整合性チェック
 * PHPのヘッダに記載されている作者と各ファイルに記載の作者が一致していることを確認する
 */
it( '[1DA1D689] Plugin author consistency check', async () => {
	// ARRANGE
	// エントリファイル(PHP)のヘッダからプラグインのバージョンを取得
	const AUTHOR = EntryPhpFile.getAuthor(); // これを正とする
	// package.jsonからプラグインの作者を取得
	const packageAuthor = require( process.cwd() + '/package.json' ).author;

	// ACT
	// Do nothing

	// ASSERT
	// PHPファイルに記載されている作者が各ファイルに記載されている作者と一致することを確認
	expect( packageAuthor ).toEqual( AUTHOR ); // package.json
} );
