const config = require( '@serendipity/config/jest-config-react' );

config.testMatch = [
	...( config.testMatch || [] ),
	'**/ts-tests/**/*.test.[jt]s?(x)', // ts-testsディレクトリもテスト対象とする
	'**/src/**/*.test.[jt]s?(x)',
];

// PHPのカバレッジも出力されるためサブディレクトリを指定
config.coverageDirectory = 'coverage/jest'; // default: `coverage`

module.exports = config;
