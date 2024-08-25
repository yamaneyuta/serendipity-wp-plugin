/** @type {import('ts-jest').JestConfigWithTsJest} **/
module.exports = {
	preset: 'ts-jest',
	testEnvironment: "jsdom",	// Reactのテストをする場合はjsdomを指定
	coverageDirectory: "coverage/jest",
};