const config = require( '@wordpress/scripts/config/webpack.config' );

if( [ 'true', '1' ].includes( process.env.CHOKIDAR_USEPOLLING ) ) {
	config.watchOptions = {
		poll: Number( process.env.CHOKIDAR_INTERVAL ),
		ignored: [ 'node_modules' ]
	};
}

module.exports = {
	...config,

	// 各エントリポイントを定義
	entry: {
		'block/index': './src/block/index.ts',
		// 'view/index': './src/view/index.tsx',
		// 'admin/index': './src/admin/index.tsx',
	},
};
