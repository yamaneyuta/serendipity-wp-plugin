/** @type {import('ts-jest').JestConfigWithTsJest} **/
module.exports = {
	preset: 'ts-jest',

	// Reactのテストを実施するため`jsdom`を指定(default: `node`)
	testEnvironment: 'jsdom',
};
