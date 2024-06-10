const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,

	// 各エントリポイントを定義
	entry: {
		'block/index': './src/block/index.ts',
		// 'view/index': './src/view/index.tsx',
		// 'admin/index': './src/admin/index.tsx',
	},
};
