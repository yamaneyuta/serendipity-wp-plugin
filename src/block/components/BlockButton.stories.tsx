import { fn, expect } from '@storybook/test';
import { BlockButton } from './BlockButton';

// More on how to set up stories at: https://storybook.js.org/docs/writing-stories#default-export
export default {
	title: 'Block/Button',
	component: BlockButton,
	parameters: {
		// Optional parameter to center the component in the Canvas. More info: https://storybook.js.org/docs/configure/story-layout
		layout: 'centered',
	},
	// This component will have an automatically generated Autodocs entry: https://storybook.js.org/docs/writing-docs/autodocs
	tags: [ 'autodocs' ],
	// More on argTypes: https://storybook.js.org/docs/api/argtypes
	argTypes: {
		backgroundColor: { control: 'color' },
	},
	// Use `fn` to spy on the onClick arg, which will appear in the actions panel once invoked: https://storybook.js.org/docs/essentials/actions#action-args
	args: {
		isBusy: false,
		disabled: false,
		children: 'Button',
		onClick: fn(),
	},
};

// More on writing stories with args: https://storybook.js.org/docs/writing-stories/args
export const Default = {
	args: {},
	play: async () => {
		expect( true ).toBe( true );
	},
};

export const IsBusy = {
	args: {
		isBusy: true,
	},
	play: async () => {
		expect( true ).toBe( true );
	},
};

export const Disabled = {
	args: {
		disabled: true,
	},
	play: async () => {
		expect( true ).toBe( true );
	},
};
