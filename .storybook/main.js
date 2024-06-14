/** @type { import('@storybook/react-webpack5').StorybookConfig } */
const config = {
	stories: ['../src/**/*.mdx', '../src/**/*.stories.@(js|jsx|mjs|ts|tsx)'],
	addons: [
		'@storybook/addon-coverage',
		'@storybook/addon-webpack5-compiler-swc',
		'@storybook/addon-onboarding',
		'@storybook/addon-links',
		'@storybook/addon-essentials',
		'@chromatic-com/storybook',
		'@storybook/addon-interactions',
	],
	framework: {
		name: '@storybook/react-webpack5',
		options: {},
	},
	previewHead: (head) => `
		${head}
		<script src="http://localhost:8888/wp-includes/js/dist/vendor/react.js"></script>
		<!-- <script src="http://localhost:8888/wp-includes/js/dist/vendor/react-dom.js"></script> -->

		<link rel="stylesheet" href="http://localhost:8888/wp-includes/css/dist/edit-post/style.css" media="all">
		<link rel="stylesheet" href="http://localhost:8888/wp-admin/css/forms.css" media="all">
		<link rel="stylesheet" href="http://localhost:8888/wp-admin/css/common.css" media="all">
		<link rel="stylesheet" href="http://localhost:8888/wp-includes/css/dist/components/style.css" media="all">
		<link rel="stylesheet" href="http://localhost:8888/wp-includes/css/dist/block-editor/style.css" media="all">
	`,
	webpackFinal: async (config) => {
		if(process.env.CHOKIDAR_USEPOLLING === "true") {
			const poll = process.env.CHOKIDAR_INTERVAL ? Number(process.env.CHOKIDAR_INTERVAL) : 300;
			config.watchOptions = {
				...config.watchOptions,
				poll,
				aggregateTimeout: 500, // 変更があってから再ビルドするまでの待ち時間
			};
		}

		// 以下のエラー対応。swc-loader が使われている場合、.inputSourceMap が必要(@storybook/addon-coverage@1.0.4 で発生)
		// Error: .inputSourceMap must be a boolean, object, or undefined
		// 以下は手抜き判定。最後の`rule`が`swc-loader`である時にparseMapを追加する。
		if(config.module.rules.slice(-1)[0].use[0].loader.includes("swc-loader")) {
			config.module.rules.slice(-1)[0].use[0].options = {
				...config.module.rules.slice(-1)[0].use[0].options,
				parseMap: true,
			}
		}
		else {
			// ここを通るときは上記手抜き判定を見直すこと。
			throw new Error("{E73A0746-7A05-425B-A806-52BD61F92851}");
		}

		// do mutate the config
		return config;
	},
};
export default config;
