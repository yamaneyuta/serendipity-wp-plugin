import { main } from '../lib/entrypoint/main';

( async () => {
	const { copiedFiles } = await main();
	for ( const file of copiedFiles ) {
		console.log( file );
	}
} )();
