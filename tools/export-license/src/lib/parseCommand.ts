import path from 'node:path';
import { parseArgs } from 'node:util';

export type CommandArgsType = {
	start: string;
	output: string;
	metaFile: string;
};

/**
 * コマンドライン引数を解析します。
 */
export const parseCommand = (): CommandArgsType => {
	const args = parseArgs( {
		options: {
			// 分析対象のディレクトリまたはファイル
			// ファイルの場合は`composer.lock`または`package-lock.json`のパスを指定
			start: {
				type: 'string',
				short: 's',
				description: 'The target to analyze directory or file',
			},
			// ライセンスファイル一式を出力するディレクトリ
			output: {
				type: 'string',
				short: 'o',
				description: 'The output directory path of the license files',
			},
			// ライセンス情報が記載されたjsonファイルの出力先
			metaFile: {
				type: 'string',
				short: 'm',
				description: 'The output file path of the license summary',
			},
		},
	} );

	let { start, output, metaFile } = args.values;

	// 入力された引数をチェック
	// - 分析対象のディレクトリが指定されていない場合はカレントディレクトリを使用
	// - ライセンスファイル一式を出力するディレクトリが指定されていない場合はエラー
	// - ライセンス情報が記載されたjsonファイルの出力先が指定されていない場合は、ライセンスファイル一式と同じディレクトリに`licenses.json`を出力
	start = start || process.cwd();
	if ( output === undefined ) {
		throw new Error( '[F5D4C6A9] `output` is required' );
	}
	if ( metaFile === undefined ) {
		metaFile = path.join( output, 'licenses.json' );
	}

	return { start, output, metaFile };
};
