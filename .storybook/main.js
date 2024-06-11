/** @type { import('@storybook/react-webpack5').StorybookConfig } */
const config = {
	stories: ['../src/**/*.mdx', '../src/**/*.stories.@(js|jsx|mjs|ts|tsx)'],
	addons: [
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
	webpackFinal: async (config) => {
		if(process.env.CHOKIDAR_USEPOLLING === "true") {
			const poll = process.env.CHOKIDAR_INTERVAL ? Number(process.env.CHOKIDAR_INTERVAL) : 300;
			config.watchOptions = {
				...config.watchOptions,
				poll,
				aggregateTimeout: 500, // 変更があってから再ビルドするまでの待ち時間
			};
		}

		// do mutate the config
		return config;
	},
};
export default config;
