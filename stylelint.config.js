/** @type {import('stylelint').Config} */
module.exports = {
	extends: '@wordpress/stylelint-config',
	ignoreFiles: [
		'**/node_modules/**',
		'public/**/*.css',
	],
};
