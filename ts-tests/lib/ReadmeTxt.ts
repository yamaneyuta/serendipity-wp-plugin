import assert from 'node:assert';
import fs from 'node:fs';
import path from 'node:path';

/**
 * readme.txtの内容を取得するクラス
 */
export class ReadmeTxt {
	private static readonly FIELD_NAME_REQUIRES_AT_LEAST = 'Requires at least'; // 最低限必要なWordPressバージョン
	private static readonly FIELD_NAME_TESTED_UP_TO = 'Tested up to'; // 最新テスト済みWordPressバージョン
	private static readonly FIELD_NAME_LICENSE = 'License'; // ライセンス

	/**
	 * 本プラグインが最低限必要とするWordPressバージョンを取得します。
	 */
	public static getRequiresAtLeast(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_REQUIRES_AT_LEAST );
	}

	/**
	 * 本プラグインがテスト済みの最新WordPressバージョンを取得します。
	 */
	public static getTestedUpTo(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_TESTED_UP_TO );
	}

	/**
	 * 本プラグインのライセンスを取得します。
	 */
	public static getLicense(): string {
		return this.getHeaderFieldsValue( this.FIELD_NAME_LICENSE );
	}

	/**
	 * 本プラグインのreadme.txtのパスを取得します。
	 */
	private static path() {
		// カレントディレクトリにあるreadme.txtファイルを取得
		return path.join( process.cwd(), 'readme.txt' );
	}

	/**
	 * 本プラグインのreadme.txtファイルの内容を行の配列で取得します。
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
		const key = `${ fieldName }:`;

		// キーとなる文字列が含まれている行を取得
		const results = this.loadLines().filter( ( l ) => l.includes( key ) );
		assert( results.length === 1 );

		// 値を取得して返す
		const ret = results[ 0 ].replace( key, '' ).trim();
		assert( ret.length > 0 );
		return ret;
	}
}
