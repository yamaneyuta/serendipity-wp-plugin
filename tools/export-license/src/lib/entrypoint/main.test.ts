import { main } from './main';
import { parseCommand } from '../parseCommand';

jest.mock( '../parseCommand' );

/**
 * license.jsonファイルがコピー先ディレクトリの一つ上の階層の場合
 */
it( 'main() - The license.json file is one level above the destination directory', async () => {
	// ARRANGE
	// ディレクトリ名が被らないようにランダムな整数を生成
	const random = Math.floor( Math.random() * 1000000 );

	( parseCommand as jest.Mock ).mockReturnValue( {
		start: process.cwd(),
		output: `/tmp/export-license-test-${ random }/output`,
		metaFile: `/tmp/export-license-test-${ random }/license.json`,
	} );

	// main()を実行し、例外が発生しないことを確認
	try {
		await main();
	} catch ( error ) {
		fail( error );
	}
} );

/**
 * license.jsonファイルがコピー先ディレクトリと同一の場合
 */
it( 'main() - The license.json file is the same as the destination directory', async () => {
	// ARRANGE
	// ディレクトリ名が被らないようにランダムな整数を生成
	const random = Math.floor( Math.random() * 1000000 );

	( parseCommand as jest.Mock ).mockReturnValue( {
		start: process.cwd(),
		output: `/tmp/export-license-test-${ random }`,
		metaFile: `/tmp/export-license-test-${ random }/license.json`,
	} );

	// main()を実行し、例外が発生しないことを確認
	try {
		await main();
	} catch ( error ) {
		fail( error );
	}
} );
