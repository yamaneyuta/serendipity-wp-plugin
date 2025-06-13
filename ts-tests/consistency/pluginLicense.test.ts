import { EntryPhpFile } from '../lib/EntryPhpFile';
import { ReadmeTxt } from '../lib/ReadmeTxt';

/**
 * プラグインライセンスの整合性チェック
 * PHPのヘッダに記載されているライセンスと各ファイルに記載のライセンスが一致していることを確認する
 */
it( '[1DA1D689] Plugin license consistency check', async () => {
	// ARRANGE
	// エントリファイル(PHP)のヘッダからプラグインのバージョンを取得
	const LICENSE = EntryPhpFile.getLicense(); // これを正とする
	// readme.txtからプラグインのライセンスを取得
	const readmeLicense = ReadmeTxt.getLicense();
	// package.jsonからプラグインのライセンスを取得
	const packageLicense = require( process.cwd() + '/package.json' ).license;

	// ACT
	// Do nothing

	// ASSERT
	// PHPファイルに記載されているライセンスが各ファイルに記載されているライセンスと一致することを確認
	expect( readmeLicense ).toEqual( LICENSE ); // readme.txt
	expect( packageLicense ).toEqual( LICENSE ); // package.json
} );
