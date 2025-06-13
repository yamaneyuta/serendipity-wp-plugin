const config = require('@yamaneyuta/serendipity-dev-conf/jest/jest.config');

if (config.testMatch !== undefined) {
	throw new Error("[0054395A] testMatch is already defined in the config. It will be overwritten.");
}
config.testMatch = [
	"**/ts-tests/**/*.test.[jt]s?(x)",
	"**/src/**/*.test.[jt]s?(x)"
];

module.exports = config;
