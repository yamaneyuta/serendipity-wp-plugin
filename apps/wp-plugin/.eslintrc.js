const config = require( '@yamaneyuta/serendipity-dev-conf/eslint/.eslintrc.js' );
config.extends.push('plugin:storybook/recommended');

config.ignorePatterns = [
	...config.ignorePatterns,
	"src/types/gql",
];

module.exports = config;
