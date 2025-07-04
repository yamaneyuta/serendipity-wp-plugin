import assert from 'node:assert';
import fs from 'node:fs';
import { md5 } from './md5';

/**
 * .asset.phpファイルを出力します。
 * @param target
 * @return 出力したファイルのパス
 */
export const exportAssetsPhp = ( target: string ) => {
	assert( target.endsWith( '.js' ) );

	// ファイルのハッシュ値を取得
	const hash = md5( fs.readFileSync( target, 'utf8' ) );
	// 出力するファイルのパスを取得
	const phpFilePath = target.replace( /\.js$/, '.asset.php' );

	// ファイルを出力
	fs.writeFileSync( phpFilePath, `<?php return array('dependencies' => array(), 'version' => '${ hash }');\n` );

	// 出力したファイルパスを返す
	return phpFilePath;
};
