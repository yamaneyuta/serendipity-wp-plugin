import fs from 'node:fs';

/**
 * 指定したファイルの変更を監視します。
 * @param file
 * @param callback
 */
export const watchFile = async ( file: string, callback: ( file: string ) => void ) => {
	while ( true ) {
		await waitFileCreated( file );

		callback( file ); // 初回実行
		const watcher = fs.watch( file, { persistent: true }, ( event, filename ) => {
			if ( ! fs.existsSync( file ) ) {
				watcher.close();
			} else {
				callback( file );
			}
		} );

		// watcherを待機
		await new Promise( ( resolve ) => {
			watcher.on( 'close', resolve );
		} );
	}
};

/**
 * 指定したミリ秒数だけ待機します。
 * @param ms
 */
const sleep = ( ms: number ) => new Promise( ( resolve ) => setTimeout( resolve, ms ) );

/**
 * ファイルが作成されるまで待機します。
 * @param file
 */
const waitFileCreated = async ( file: string ) => {
	while ( true ) {
		if ( fs.existsSync( file ) ) {
			return;
		}
		await sleep( 500 );
	}
};
