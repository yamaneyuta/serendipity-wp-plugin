const config = require( '@serendipity/config/eslint' );
config.extends.push( 'plugin:storybook/recommended' );

config.ignorePatterns = [ ...config.ignorePatterns, 'src/types/gql' ];

// storybook関連のdevDependenciesをルートから参照することを許可
config.rules = {
	...config.rules,
	'import/no-extraneous-dependencies': [
		'error',
		{
			devDependencies: true,
			packageDir: [ '.', '../..' ], // ワークスペースルートも検索対象に追加
		},
	],
};

module.exports = config;
