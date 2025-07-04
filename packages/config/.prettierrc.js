let config = require( '@wordpress/prettier-config' );

config = {
	...config,
	printWidth: 120,
	overrides: [
		...( config.overrides || [] ),
		{
			files: '*.yml',
			options: {
				tabWidth: 2,
				useTabs: false,
				singleQuote: false,
			},
		},
		{
			files: '*.sol',
			options: {
				singleQuote: false,
			},
		},
		{
			files: [ 'package.json', 'package-lock.json' ],
			options: {
				tabWidth: 2,
				useTabs: false,
			},
		},
	],
};
module.exports = config;
