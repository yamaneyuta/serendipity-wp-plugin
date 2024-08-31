/** @type {import('ts-jest').JestConfigWithTsJest} **/
module.exports = {
	preset: 'ts-jest',

	// 基本的にReactのテストを実施するため`jsdom`を指定(default: `node`)
	testEnvironment: 'jsdom',

	// カバレッジ出力先(default: `coverage`)
	coverageDirectory: 'coverage/jest',
};
