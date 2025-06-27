import { expect } from '@jest/globals';
import React from 'react';
import { render } from '../../../../jest-lib/render';
import { usePostIDFromDom } from './usePostIDFromDom';

const TEST_ID = '6CEC0231';

const Sut: React.FC = () => {
	const postID = usePostIDFromDom();
	return (
		<>
			<p data-testid={ TEST_ID }>{ String( postID ) }</p>
		</>
	);
};

/**
 * `usePostIDFromDom`のテスト
 */
describe( '[BA9B42CB] usePostIDFromDom()', () => {
	const cleanup = () => {
		document.body.innerHTML = '';
	};
	beforeEach( cleanup );
	afterEach( cleanup );

	it( '[8F73E331] should return postID from DOM', () => {
		// ARRANGE
		document.body.innerHTML = '<input type="hidden" id="post_ID" name="post_ID" value="42" />';

		// ACT
		const { getByTestId } = render( <Sut /> );

		// ASSERT
		const postID = getByTestId( TEST_ID ).textContent;
		expect( postID ).toBe( '42' );
	} );

	it( '[7AF2B0AE] should return null when post_ID does not exist', () => {
		// ARRANGE

		// ACT
		const { getByTestId } = render( <Sut /> );

		// ASSERT
		const postID = getByTestId( TEST_ID ).textContent;
		expect( postID ).toBe( 'null' );
	} );
} );
