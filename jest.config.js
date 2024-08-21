/** @type {import('ts-jest').JestConfigWithTsJest} **/
module.exports = {
	testEnvironment: "jsdom",	// Reactのテストをする場合はjsdomを指定
	transform: {
		// `lib-frontend`のライブラリ読み込み時のエラーを解消するために
		// `"allowJs": true`を指定した`tsconfig.jest.json`を使用
	    "^.+\\.m?[tj]sx?$": ['ts-jest', { tsconfig: "tsconfig.jest.json" }],
	},
	// `lib-frontend`ライブラリがトランスパイルを必要とするが、`node_modules`にあるため、設定を追加
	transformIgnorePatterns: ["node_modules/(?!.*(lib-frontend))"],

	coverageDirectory: "coverage/jest",
};