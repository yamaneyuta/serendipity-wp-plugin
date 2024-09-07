import assert from 'node:assert';
import fs from 'node:fs';
import path from 'node:path';
import { globSync } from 'glob'; // eslint-disable-line import/no-extraneous-dependencies

/**
 * 本プラグインのエントリポイントとなるPHPファイルの情報を取得するクラス。
 */
export class EntryPhpFile {
	private static readonly FIELD_NAME_VERSION = 'Version'; // プラグインバージョン
	private static readonly FIELD_NAME_REQUIRES_AT_LEAST = 'Requires at least'; // 最低限必要なWordPressバージョン
	private static readonly FIELD_NAME_TEXT_DOMAIN = 'Text Domain'; // テキストドメイン
	private static readonly FIELD_NAME_LICENSE = 'License'; // ライセンス
	private static readonly FIELD_NAME_AUTHOR = 'Author'; // 作者
	private static readonly FIELD_NAME_REQUIRES_PHP = 'Requires PHP'; // 最低限必要なPHPバージョン

	/**
	 * 本プラグインのバージョンを取得します。
	 */
	public static getVersion(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_VERSION );
	}

	/**
	 * 本プラグインが最低限必要とするWordPressバージョンを取得します。
	 */
	public static getRequiresAtLeast(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_REQUIRES_AT_LEAST );
	}

	/**
	 * 本プラグインのテキストドメインを取得します。
	 */
	public static getTextDomain(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_TEXT_DOMAIN );
	}

	/**
	 * 本プラグインのライセンスを取得します。
	 */
	public static getLicense(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_LICENSE );
	}

	/**
	 * 本プラグインの作者を取得します。
	 */
	public static getAuthor(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_AUTHOR );
	}

	/**
	 * 本プラグインが最低限必要とするPHPバージョンを取得します。
	 */
	public static getRequiresPHP(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_REQUIRES_PHP );
	}

	/**
	 * 本プラグインのエントリポイントとなるPHPファイルのパスを取得します。
	 */
	private static path() {
		// カレントディレクトリにあるPHPファイルを取得
		const phpFiles = globSync( path.join( process.cwd(), '*.php' ) );
		assert( phpFiles.length === 1 );

		return phpFiles[ 0 ];
	}

	/**
	 * 本プラグインのエントリポイントとなるPHPファイルの内容を行の配列で取得します。
	 */
	private static loadLines(): string[] {
		const filePath = this.path();
		return fs.readFileSync( filePath, 'utf-8' ).split( '\n' );
	}

	/**
	 * 指定されたフィールド名に対応する設定値を取得します。
	 * @param fieldName
	 */
	private static getHeaderFieldsValue( fieldName: string ): string {
		// ヘッダ内に記載されるフィールドのキーに相当する文字列に変換
		const key = `* ${ fieldName }:`;

		// キーとなる文字列が含まれている行を取得
		const results = this.loadLines().filter( ( l ) => l.includes( key ) );
		assert( results.length === 1 );

		// 値を取得して返す
		const ret = results[ 0 ].replace( key, '' ).trim();
		assert( ret.length > 0 );
		return ret;
	}
}
