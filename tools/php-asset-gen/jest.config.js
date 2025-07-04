/** @type {import('ts-jest').JestConfigWithTsJest} */
module.exports = {
    preset: 'ts-jest',
    testEnvironment: 'node',

    // distディレクトリは対象外
    testPathIgnorePatterns: [
        "/node_modules/",
        "/dist/"
    ]
};
