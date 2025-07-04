import { parseArgs } from 'node:util';

export type CommandArgsType = {
	file: string;
	watch: boolean;
};

/**
 * コマンドライン引数を解析します。
 */
export const parseCommand = (): CommandArgsType => {
	const args = parseArgs( {
		options: {
			// .asset.phpファイルを出力ための対象ファイル(.js)
			file: {
				type: 'string',
				short: 'f',
				description: 'The target file (.js) to output the .asset.php file',
			},
			// watchモードで実行するかどうか
			watch: {
				type: 'boolean',
				short: 'w',
				description: 'Whether to run in watch mode',
				default: false,
			},
		},
	} );

	let { file, watch } = args.values;

	// 入力された引数をチェック
	if ( file === undefined ) {
		throw new Error( '[2D6F4623] `file` is required' );
	}
	watch = watch || false;

	return { file, watch };
};
