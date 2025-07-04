// @ts-check
const fs = require( 'fs' );
const path = require( 'path' );

const config = require( '@wordpress/eslint-plugin/configs/recommended' );

config.rules = {
	...config.rules,

	// `console.warn`及び`console.error`の使用を許可(console.logはエラー)
	'no-console': [ 'error', { allow: [ 'warn', 'error' ] } ],

	// `if`ブロック内で`return`がある場合、`else`ブロックを省略する設定を`off`に変更
	'no-else-return': 'off',

	'jsdoc/check-tag-names': [
		'error',
		{
			// @remarksタグを許可
			definedTags: [ 'remarks' ],
		},
	],
};

// 現在のディレクトリに.prettierignoreが存在する場合、それを無視する設定を追加
const prettierIgnorePath = path.join( process.cwd(), '.prettierignore' );
if ( fs.existsSync( prettierIgnorePath ) ) {
	config.ignorePatterns =
		typeof config.ignorePatterns === 'string' ? [ config.ignorePatterns ] : config.ignorePatterns || [];
	const prettierIgnoreContent = fs.readFileSync( prettierIgnorePath, 'utf8' );
	const ignorePatterns = prettierIgnoreContent
		.split( '\n' )
		.filter( ( line ) => line.trim().length > 0 && ! line.trim().startsWith( '#' ) )
		.map( ( pattern ) => pattern.trim() );
	config.ignorePatterns.push( ...ignorePatterns );
}

config.settings[ 'import/resolver' ] = {
	// `eslint-import-resolver-typescript`
	// -> `import/named`を解決
	typescript: {},
};

// `import/no-extraneous-dependencies`ルールの設定を追加
// => pnpmのワークスペースを使用しているので、モノレポのディレクトリもpackageDirに追加する必要がある
// ワークスペースルートも検索対象に追加するため、`pnpm-lock.yaml`ファイルが存在するディレクトリまでを検索する
/** @type {string[]} */
const noExtraneousDependenciesPackagesDir = ( () => {
	// ルートディレクトリで実行した場合は、現在のディレクトリを返す
	if ( fs.existsSync( path.join( process.cwd(), 'pnpm-lock.yaml' ) ) ) {
		return [ '.' ];
	}

	// その他のディレクトリの場合は、親ディレクトリを辿っていき、`pnpm-lock.yaml`が存在するディレクトリを追加する
	let dir = process.cwd();
	const rootDir = path.parse( process.cwd() ).root;
	while ( dir !== rootDir ) {
		if ( fs.existsSync( path.join( dir, 'pnpm-lock.yaml' ) ) ) {
			return [ '.', dir ];
		}
		dir = path.dirname( dir );
	}
	throw new Error( '[3611AE6E] pnpm-lock.yaml not found in any parent directory.' );
} )();

config.rules = {
	...config.rules,
	'import/no-extraneous-dependencies': [
		'error',
		{
			devDependencies: true,
			packageDir: noExtraneousDependenciesPackagesDir,
		},
	],
};

module.exports = config;
