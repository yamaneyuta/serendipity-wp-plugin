const fs = require( 'fs' );
const path = require( 'path' );

/** @type {import('eslint').ESLint.ConfigData} */
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

module.exports = config;
