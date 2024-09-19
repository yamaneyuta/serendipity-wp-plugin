import type { Meta, StoryObj } from '@storybook/react';
import { fn, expect, within } from '@storybook/test';
import { BlockButton } from './BlockButton';

// More on how to set up stories at: https://storybook.js.org/docs/writing-stories#default-export
const meta: Meta = {
	title: 'Components/BlockButton',
	component: BlockButton,
	tags: [ 'autodocs' ],
	argTypes: {
		isBusy: { type: 'boolean' },
		disabled: { type: 'boolean' },
		onClick: { action: 'click' },
	},
	args: {
		isBusy: undefined,
		disabled: undefined,
		children: 'Button',
		onClick: fn(),
	},
};
export default meta;
type Story = StoryObj< typeof BlockButton >;

// More on writing stories with args: https://storybook.js.org/docs/writing-stories/args
export const Default: Story = {
	args: {
		onClick: fn(),
	},
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
		onClick: fn(),
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
		onClick: fn(),
	},
	play: async ( { args, canvasElement } ) => {
		const canvas = within( canvasElement );
		canvas.getByRole( 'button' ).click();
		// ボタンがクリックされていないことを確認
		expect( args.onClick ).not.toHaveBeenCalled();
	},
};
