const config = require( '@serendipity/config/eslint' );
module.exports = {
    ...config,
    rules: {
        ...config.rules,
        // コマンドラインアプリとして作成するため、no-console を無効化
        'no-console': 'off',
    },
};
