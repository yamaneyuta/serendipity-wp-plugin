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

config.settings[ 'import/resolver' ] = {
	// `eslint-import-resolver-typescript`
	// -> `import/named`を解決
	typescript: {},
};

module.exports = config;
