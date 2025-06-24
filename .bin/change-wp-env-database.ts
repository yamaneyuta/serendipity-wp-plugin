import fs from 'node:fs';

// 環境変数からDATABASE_IMAGEを取得
const databaseImage = process.env.DATABASE_IMAGE;
if ( ! databaseImage ) {
	throw new Error( '[C6825346] DATABASE_IMAGE is not set in the environment variables.' );
}

// `node_modules/@wordpress/env/lib/build-docker-compose-config.js`の`mariadb:lts`を置き換える
// 参考: https://github.com/WordPress/gutenberg/issues/59232#issuecomment-1956496052
const filePath = 'node_modules/@wordpress/env/lib/build-docker-compose-config.js';
if ( ! fs.existsSync( filePath ) ) {
	throw new Error( `[420A61B6] File not found: ${ filePath }` );
}

const fileContent = fs.readFileSync( filePath, 'utf8' );
const updatedContent = fileContent.replace( /mariadb:lts/g, databaseImage );
// 置換後の文字列が含まれていない場合はエラー
if ( ! updatedContent.includes( databaseImage ) ) {
	throw new Error( '[8682EF61] Replacement failed. Please check the file content.' );
}
fs.writeFileSync( filePath, updatedContent, 'utf8' );
