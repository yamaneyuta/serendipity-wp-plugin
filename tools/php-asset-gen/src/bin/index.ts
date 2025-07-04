import { parseCommand } from '../lib/parseCommand';
import { exportAssetsPhp } from '../lib/exportAssetPhp';
import { watchFile } from '../lib/watchFile';

/**
 * .asset.phpファイルを出力し、ログを出力します。
 * @param file
 */
const exportAssetAndLog = ( file: string ) => {
	const exported = exportAssetsPhp( file );
	console.log( `Exported: ${ exported }` );
};

const main = async () => {
	const { file, watch } = parseCommand();
	if ( watch ) {
		await watchFile( file, ( f ) => exportAssetAndLog( f ) );
	} else {
		exportAssetAndLog( file );
	}
};
( async () => {
	try {
		await main();
	} catch ( error ) {
		if ( error instanceof Error ) {
			console.error( error.message );
		} else {
			console.error( error );
		}
	}
} )();
