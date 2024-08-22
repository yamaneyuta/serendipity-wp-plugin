import { ComponentProps } from 'react';
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

/**
 * ボタンがクリックされたときに呼び出される関数を初期化します。
 *
 * ※ Storybook上は不要だが、jestから呼び出した時に`toHaveBeenCalled`が期待通りに動作しなかったため追加
 * @param args
 */
const clearOnClick = ( args: ComponentProps< typeof BlockButton > ) => {
	( args.onClick as ReturnType< typeof fn > ).mockClear();
};

// More on writing stories with args: https://storybook.js.org/docs/writing-stories/args
export const Default: Story = {
	args: {},
	play: async ( { args, canvasElement } ) => {
		clearOnClick( args );
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
		clearOnClick( args );
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
		clearOnClick( args );
		const canvas = within( canvasElement );
		canvas.getByRole( 'button' ).click();
		// ボタンがクリックされていないことを確認
		expect( args.onClick ).not.toHaveBeenCalled();
	},
};
