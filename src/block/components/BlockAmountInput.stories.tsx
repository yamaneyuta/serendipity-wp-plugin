import type { Meta, StoryObj } from '@storybook/react';
import { fn, expect, userEvent, within } from '@storybook/test';
import { BlockAmountInput, TEST_ID } from './BlockAmountInput';

const meta: Meta< typeof BlockAmountInput > = {
	title: 'Components/BlockAmountInput',
	component: BlockAmountInput,
	parameters: {
		// Optional parameter to center the component in the Canvas. More info: https://storybook.js.org/docs/configure/story-layout
		// layout: 'centered',
	},
	tags: [ 'autodocs' ],
	argTypes: {
		disabled: { type: 'boolean' },
	},
	args: {
		disabled: undefined,
	},
};
export default meta;
type Story = StoryObj< typeof BlockAmountInput >;

export const Default: Story = {
	args: {},
	play: async ( { args, canvasElement } ) => {
		const canvas = within( canvasElement );
		const input = canvas.getByTestId( TEST_ID );
		await userEvent.type( input, 'Hello, World!' );
		// 通常の文字列は入力できていないことを確認
		expect( input ).toHaveValue( null );

		await userEvent.type( input, '12345.6789' );
		// 数字のみ入力できていることを確認
		expect( input ).toHaveValue( 12345.6789 );

		// 内容をクリア
		await userEvent.clear( input );

		// +, -, e は入力できないことを確認
		await userEvent.type( input, '+' );
		expect( input ).toHaveValue( null );

		await userEvent.type( input, '-' );
		expect( input ).toHaveValue( null );

		await userEvent.type( input, 'e' );
		expect( input ).toHaveValue( null );
	},
};

export const Disabled: Story = {
	args: {
		disabled: true,
	},
	play: async ( { args, canvasElement } ) => {
		const canvas = within( canvasElement );
		const input = canvas.getByTestId( TEST_ID ) as HTMLInputElement;
		input.disabled = true;
		await userEvent.type( input, '12345.6789' );
		// disabled属性が設定されているため、入力できていないことを確認
		expect( input ).toHaveTextContent( '' );
	},
};
