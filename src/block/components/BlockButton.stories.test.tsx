import { composeStories } from '@storybook/react';
import { render } from '@testing-library/react';
import * as stories from './BlockButton.stories';

/**
 * BlockButton.stories.tsxのテスト
 */
describe( '[52827D04] BlockButton', () => {
	it( '[41A1F49D] Default', async () => {
		const { Default } = composeStories( stories );
		const { container } = render( <Default /> );
		await Default.play( { canvasElement: container } );
	} );

	it( '[5B536589] IsBusy', async () => {
		const { IsBusy } = composeStories( stories );
		const { container } = render( <IsBusy /> );
		await IsBusy.play( { canvasElement: container } );
	} );

	it( '[AAC4BDDD] Disabled', async () => {
		const { Disabled } = composeStories( stories );
		const { container } = render( <Disabled /> );
		await Disabled.play( { canvasElement: container } );
	} );
} );
