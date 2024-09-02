const config = require( '@yamaneyuta/serendipity-dev-conf/eslint/.eslintrc.js' );
module.exports = {
    ...config,
    rules: {
        ...config.rules,
        // コマンドラインアプリとして作成するため、no-console を無効化
        'no-console': 'off',
    },
};
