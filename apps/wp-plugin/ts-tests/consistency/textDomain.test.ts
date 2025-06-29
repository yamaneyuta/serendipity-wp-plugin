import { describe, expect, it } from '@jest/globals';
import fs from 'node:fs';
import path from 'node:path';
import { EntryPhpFile } from '../lib/EntryPhpFile';

/**
 * 本プラグインのテキストドメイン
 * package.jsonの`name`プロパティを正とする。
 */
// const TEXT_DOMAIN = require( process.cwd() + '/package.json' ).name as string;

/**
 * テキストドメインの整合性チェック
 */
describe( '[3CC23DEC] Text Domain consistency check', () => {
	/**
	 * テキストドメインの文字列が正しい形式であることを確認
	 *
	 * プラグインがディレクトリに展開される場合はそのディレクトリ名と一致させる。
	 * (WordPress.orgにホストされている場合はslugと一致させる。)
	 * => 開発環境は`workspace`で作業しているため、テストは実施しない。
	 *
	 * テキストドメインは、小文字及びハイフンで構成される。
	 * https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#text-domains
	 * > The text domain name must use dashes and not underscores, be lower case, and have no spaces.
	 */
	it( '[2AB379FB] Text Domain format', async () => {
		// ARRANGE
		const TEXT_DOMAIN = EntryPhpFile.getTextDomain();

		// ACT
		// Do nothing

		// ASSERT
		expect( TEXT_DOMAIN ).toMatch( /^[a-z-]+$/ );
	} );

	/**
	 * [Text Domain].phpファイルが存在することを確認
	 */
	it( '[E6F3D6A4] Text Domain - exists PHP file', async () => {
		// ARRANGE
		const TEXT_DOMAIN = EntryPhpFile.getTextDomain();
		const filePath = path.resolve( process.cwd(), TEXT_DOMAIN + '.php' );

		// ACT
		const exists = fs.existsSync( filePath );

		// ASSERT
		expect( exists ).toBe( true );
	} );

	/**
	 * package.jsonのnameプロパティとエントリポイントとなるPHPファイルヘッダに記載のテキストドメインが一致することを確認
	 */
	it( '[24E10DE5] Text Domain - package.json::name', async () => {
		// ARRANGE
		const TEXT_DOMAIN = EntryPhpFile.getTextDomain();
		const name = require( process.cwd() + '/package.json' ).name;

		// ACT
		// Do nothing

		// ASSERT
		expect( name ).toBe( TEXT_DOMAIN );
	} );

	/**
	 * block.jsonのtextdomainプロパティがテキストドメインと一致していることを確認
	 */
	it( '[3F571464] Text Domain - block.json (textdomain)', async () => {
		// ARRANGE
		const TEXT_DOMAIN = EntryPhpFile.getTextDomain();
		const textdomain: string = require( process.cwd() + '/src/block/block.json' ).textdomain;

		// ACT
		// Do nothing

		// ASSERT
		expect( textdomain ).toBe( TEXT_DOMAIN );
	} );

	/**
	 * block.jsonの`name`プロパティがテキストドメインの文字列で終わっていることを確認
	 */
	it( '[1AD656F5] Text Domain - block.json (name)', async () => {
		// ARRANGE
		const TEXT_DOMAIN = EntryPhpFile.getTextDomain();
		const name: string = require( process.cwd() + '/src/block/block.json' ).name;

		// ACT
		// Do nothing

		// ASSERT
		expect( name.endsWith( TEXT_DOMAIN ) ).toBe( true );
	} );
} );
