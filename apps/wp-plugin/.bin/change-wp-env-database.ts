import fs from 'node:fs';
import * as os from 'node:os';

// このスクリプトは、wp-envで使用するデータベースのイメージを変更します。
// 参考: https://github.com/WordPress/gutenberg/issues/59232#issuecomment-1956496052

// ファイルのバックアップを作成します
const createBackupFile = ( filePath: string ) => {
	const backupFilePath = `${ filePath }.bak`;
	if ( ! fs.existsSync( backupFilePath ) ) {
		fs.copyFileSync( filePath, backupFilePath );
	}
};

// 起動するデータベースイメージを書き換えます
// - 元のJSファイルは`mariadb:lts`固定のため、それを置換
const rewriteDatabaseImage = ( targetFilePath: string, imageName: string ) => {
	let fileContent = fs.readFileSync( targetFilePath, 'utf8' );
	fileContent = fileContent.replace( /mariadb:lts/g, imageName );

	// 置換後の文字列が含まれていない場合はエラー(複数回呼ばれても問題がないように書き換え後をチェック)
	if ( ! fileContent.includes( imageName ) ) {
		throw new Error( '[8682EF61] Replacement failed. Please check the file content.' );
	}

	// 上書き保存
	fs.writeFileSync( targetFilePath, fileContent, 'utf8' );
};

// mysqlコンテナのvolumesの書き換えを行います
// - volumes: [ 'mysql:/var/lib/mysql' ], ⇒ volumes: [ 'mysql:/var/lib/mysql', '/home/vscode/my.cnf:/etc/mysql/conf.d/my.cnf' ],
// - volumes: [ 'mysql-test:/var/lib/mysql' ], ⇒ volumes: [ 'mysql-test:/var/lib/mysql', '/home/vscode/my.cnf:/etc/mysql/conf.d/my.cnf' ],
const rewriteMySqlVolumes = ( targetFilePath: string, myCnfPath: string ) => {
	let fileContent = fs.readFileSync( targetFilePath, 'utf8' );
	// 複数回呼ばれても問題ないように書き換え後の文字列が含まれていない時にだけ処理を実行
	if ( ! fileContent.includes( myCnfPath ) ) {
		fileContent = fileContent.replace(
			/:\/var\/lib\/mysql'/g,
			`:/var/lib/mysql', '${ myCnfPath }:/etc/mysql/conf.d/my.cnf'`
		);
		// 上書き保存
		fs.writeFileSync( targetFilePath, fileContent, 'utf8' );
	}
};

/**
 * cliコンテナのvolumesの書き換えを行います
 * @param      targetFilePath
 * @param      myCnfPath
 * @deprecated 現在の内容では不正なyamlが生成されるため、使用しないでください。
 */
const rewriteCliVolumes = ( targetFilePath: string, myCnfPath: string ) => {
	const fileLines = fs.readFileSync( targetFilePath, 'utf8' ).split( '\n' );

	let isCliSection = false;
	let isTestsCliSection = false;
	for ( let i = 0; i < fileLines.length; i++ ) {
		const line = fileLines[ i ];

		if ( line.endsWith( 'cli: {' ) ) {
			isCliSection = true;
		} else if ( isCliSection && line.trim() === 'volumes: developmentMounts,' ) {
			fileLines[ i ] = fileLines[ i ].replace(
				'volumes: developmentMounts,',
				`volumes: [ ...developmentMounts, '${ myCnfPath }:/etc/mysql/conf.d/my.cnf' ],`
			);
			isCliSection = false;
		}

		if ( line.trim() === "'tests-cli': {" ) {
			isTestsCliSection = true;
		} else if ( isTestsCliSection && line.trim() === 'volumes: testsMounts,' ) {
			fileLines[ i ] = fileLines[ i ].replace(
				'volumes: testsMounts,',
				`volumes: [ ...testsMounts, '${ myCnfPath }:/etc/mysql/conf.d/my.cnf' ],`
			);
			isTestsCliSection = false;
		}
	}
	// 上書き保存
	fs.writeFileSync( targetFilePath, fileLines.join( '\n' ), 'utf8' );

	throw new Error( '[98937C43] This function is deprecated and should not be used.' );
};

const createMyCnfFile = ( myCnfPath: string, imageName: string ) => {
	// # my.cnfを参照させる理由
	// ## 開発用に高速化可能な設定を追加
	//     生成される`docker-compose.yml`を見る限り、素の状態でコンテナを起動しているので
	//     開発及びテスト環境に適した設定を行い、パフォーマンスを向上させる。
	// ## MySQLをテスト【未解決】
	//     jsファイルの書き換えによりwp-envで起動するデータベースを変更することができるようになったが、
	//     MySQLのイメージ(8.0以降)を指定した時にエラーが発生した。このエラーを解消するのが目的。
	//     以下は原因調査時のメモ。
	//       - cliイメージがalpineベースのため、mysqlクライアントがMariaDBのものしか提供されていない
	//       - MySQL側は認証に`caching_sha2_password`を使用するが、MariaDBクライアントはそれを使用できない
	//       - MySQL側の認証方式を`mysql_native_password`にしたいが、新しいバージョンのMySQLでは`mysql_native_password`プラグインが無効化されている
	//       - `mysql_native_password`プラグインをクエリやdocker-composeのcommandで有効化することはできない
	//       - `mysql_native_password`を使うようにする書き方はバージョンによって異なる(https://blog.s-style.co.jp/2024/05/11793/)

	const tag = imageName.split( ':' )[ 1 ] || 'latest'; // イメージ名からタグを取得。例: `mariadb:lts` => `lts`

	const mysqldSettings = [ '[mysqld]' ];
	const clientSettings = [ '[client]' ];

	// TODO: 高速化のためのサーバー設定をここに記述

	fs.writeFileSync( myCnfPath, [ ...mysqldSettings, '', ...clientSettings, '' ].join( '\n' ), 'utf8' );
};

// main
( async () => {
	// 書き換え対象となるファイルのパス。このファイルの`image`や`volumes`を書き換えてwp-envが参照するdocker-compose.ymlを変更する
	// 参考: https://github.com/WordPress/gutenberg/issues/59232#issuecomment-1956496052
	const TARGET_JS_FIRE_PATH = 'node_modules/@wordpress/env/lib/build-docker-compose-config.js';

	// 起動するデータベースイメージ名
	const DATABASE_IMAGE: string | undefined = process.env.DATABASE_IMAGE;

	// 作成するmy.cnfのパス(docker-composeのvolumeに記述するため、絶対パスで指定。チルダ使用不可)
	const MY_CNF_PATH = `${ os.homedir() }/my.cnf`;

	if ( ! DATABASE_IMAGE ) {
		throw new Error( '[C6825346] DATABASE_IMAGE is not set in the environment variables.' );
	} else if ( ! DATABASE_IMAGE.includes( 'mysql' ) && ! DATABASE_IMAGE.includes( 'mariadb' ) ) {
		throw new Error( `[37DF5426] DATABASE_IMAGE must be either a MySQL or MariaDB image. - ${ DATABASE_IMAGE }` );
	} else if ( ! fs.existsSync( TARGET_JS_FIRE_PATH ) ) {
		throw new Error( `[420A61B6] File not found: ${ TARGET_JS_FIRE_PATH }` );
	}

	// ファイルのバックアップ
	createBackupFile( TARGET_JS_FIRE_PATH );

	// my.cnfの作成
	createMyCnfFile( MY_CNF_PATH, DATABASE_IMAGE );

	// jsファイルの書き換え
	rewriteDatabaseImage( TARGET_JS_FIRE_PATH, DATABASE_IMAGE );
	rewriteMySqlVolumes( TARGET_JS_FIRE_PATH, MY_CNF_PATH );
	// rewriteCliVolumes( TARGET_JS_FIRE_PATH, MY_CNF_PATH );
} )().catch( ( error ) => {
	console.error( error );
	process.exit( 1 );
} );
