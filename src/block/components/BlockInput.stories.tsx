import type { Meta, StoryObj } from '@storybook/react';
import { fn, expect, userEvent, within } from '@storybook/test';
import { BlockInput } from './BlockInput';

// More on how to set up stories at: https://storybook.js.org/docs/writing-stories#default-export
const meta: Meta = {
	title: 'Components/BlockInput',
	component: BlockInput,
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
		disabled: undefined,
	},
};
export default meta;
type Story = StoryObj< typeof BlockInput >;

// More on writing stories with args: https://storybook.js.org/docs/writing-stories/args
export const Default: Story = {
	args: {},
	play: async ( { args, canvasElement } ) => {
		const canvas = within( canvasElement );
		const input = canvas.getByRole( 'textbox' );
		await userEvent.type( input, 'Hello, World!' );
		// 入力された文字列が正しいことを確認
		expect( input ).toHaveValue( 'Hello, World!' );
	},
};

export const Disabled: Story = {
	args: {
		disabled: true,
	},
	play: async ( { args, canvasElement } ) => {
		const canvas = within( canvasElement );
		const input = canvas.getByRole( 'textbox' );
		await userEvent.type( input, 'Hello, World!' );
		// 文字列が入力されないことを確認
		expect( input ).toHaveValue( '' );
	},
};
