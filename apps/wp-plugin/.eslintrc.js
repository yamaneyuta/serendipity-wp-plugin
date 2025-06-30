const config = require( '@serendipity/config/eslint' );
config.extends.push('plugin:storybook/recommended');

config.ignorePatterns = [
	...config.ignorePatterns,
	"src/types/gql",
];

module.exports = config;
