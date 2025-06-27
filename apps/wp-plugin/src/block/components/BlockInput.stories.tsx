import type { Meta, StoryObj } from '@storybook/react';
import { expect, userEvent, within } from '@storybook/test';
import { BlockInput } from './BlockInput';

// More on how to set up stories at: https://storybook.js.org/docs/writing-stories#default-export
const meta: Meta = {
	title: 'Components/BlockInput',
	component: BlockInput,
	tags: [ 'autodocs' ],
	argTypes: {
		disabled: { type: 'boolean' },
	},
	args: {
		disabled: undefined,
	},
};
export default meta;
type Story = StoryObj< typeof BlockInput >;

// More on writing stories with args: https://storybook.js.org/docs/writing-stories/args
export const Default: Story = {
	args: {},
	play: async ( { canvasElement } ) => {
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
	play: async ( { canvasElement } ) => {
		const canvas = within( canvasElement );
		const input = canvas.getByRole( 'textbox' );
		await userEvent.type( input, 'Hello, World!' );
		// 文字列が入力されないことを確認
		expect( input ).toHaveValue( '' );
	},
};
