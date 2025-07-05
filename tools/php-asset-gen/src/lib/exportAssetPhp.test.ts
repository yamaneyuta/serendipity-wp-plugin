import fs from 'node:fs';
import { exportAssetsPhp } from './exportAssetPhp';
import { it, expect } from '@jest/globals';

/**
 * 対象のjavascriptファイルが存在する場合、.asset.phpファイルが出力されることを確認
 */
it( '[BDDAED52] exportAssetsPhp - file exists', async () => {
	// ARRANGE
	// 現在時刻からファイル名を生成
	const target = `./test-${ Date.now() }.js`;
	// 空のファイルを作成
	fs.writeFileSync( target, '' );

	// ACT
	// .asset.phpファイルを出力
	const result = exportAssetsPhp( target );

	// ASSERT
	// 空文字のMD5ハッシュ値: d41d8cd98f00b204e9800998ecf8427e
	expect( fs.readFileSync( result, 'utf8' ) ).toBe(
		`<?php return array('dependencies' => array(), 'version' => 'd41d8cd98f00b204e9800998ecf8427e');\n`
	);

	// 生成したファイルを削除
	fs.unlinkSync( target );
	fs.unlinkSync( result );
} );

/**
 * 対象のjavascriptファイルが存在しない場合、エラーが発生することを確認
 */
it( '[F40EF2BF] exportAssetsPhp - file not exists', async () => {
	// ARRANGE
	// 現在時刻からファイル名を生成
	const target = `./test-${ Date.now() }.js`;

	// ACT, ASSERT
	expect( () => exportAssetsPhp( target ) ).toThrow( 'ENOENT: no such file or directory' );
} );
