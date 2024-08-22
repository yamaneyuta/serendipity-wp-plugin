import { composeStories } from '@storybook/react';
import { render } from '@testing-library/react';
import * as stories from './BlockInput.stories';

/**
 * BlockInput.stories.tsxのテスト
 */
describe( '[846DF720] BlockInput', () => {
	it( '[4A1AF20F] Default', async () => {
		const { Default } = composeStories( stories );
		const { container } = render( <Default /> );
		await Default.play( { canvasElement: container } );
	} );

	it( '[06C196EE] Disabled', async () => {
		const { Disabled } = composeStories( stories );
		const { container } = render( <Disabled /> );
		await Disabled.play( { canvasElement: container } );
	} );
} );
