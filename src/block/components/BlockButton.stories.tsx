import type { Meta, StoryObj } from '@storybook/react';
import { fn, expect, within } from '@storybook/test';
import { BlockButton } from './BlockButton';

// More on how to set up stories at: https://storybook.js.org/docs/writing-stories#default-export
const meta: Meta = {
	title: 'Components/BlockButton',
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
export default meta;
type Story = StoryObj< typeof BlockButton >;

// More on writing stories with args: https://storybook.js.org/docs/writing-stories/args
export const Default: Story = {
	args: {},
	play: async ( { args, canvasElement } ) => {
		const canvas = within( canvasElement );
		canvas.getByRole( 'button' ).click();
		// ボタンがクリックされたことを確認
		expect( args.onClick ).toHaveBeenCalled();
	},
};

export const IsBusy: Story = {
	args: {
		isBusy: true,
	},
	play: async ( { args, canvasElement } ) => {
		const canvas = within( canvasElement );
		canvas.getByRole( 'button' ).click();
		// ボタンがクリックされていないことを確認
		expect( args.onClick ).not.toHaveBeenCalled();
	},
};

export const Disabled: Story = {
	args: {
		disabled: true,
	},
	play: async ( { args, canvasElement } ) => {
		const canvas = within( canvasElement );
		canvas.getByRole( 'button' ).click();
		// ボタンがクリックされていないことを確認
		expect( args.onClick ).not.toHaveBeenCalled();
	},
};
