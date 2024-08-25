import { usePostID } from './usePostID';
import { render } from '../../../../../jest-lib/render';
import { usePostIDFromDom } from '../../../lib/postID/usePostIDFromDom';
import { PostIDProvider } from './PostIDProvider';

jest.mock( '../../../lib/postID/usePostIDFromDom' );

const TEST_ID = 'F905B9E0';

const Sut: React.FC = () => {
	const postID = usePostID();
	return (
		<>
			<p data-testid={ TEST_ID }>{ String( postID ) }</p>
		</>
	);
};

describe( '[AD435E36] usePostID()', () => {
	/**
	 * postIDが取得できる場合のテスト
	 */
	it( '[8F2368E8] should return postID from DOM', () => {
		// ARRANGE
		( usePostIDFromDom as jest.Mock ).mockReturnValue( 42 );

		// ACT
		const { getByTestId } = render(
			<PostIDProvider>
				<Sut />
			</PostIDProvider>
		);
		const postID = getByTestId( TEST_ID ).textContent;

		// ASSERT
		expect( postID ).toBe( '42' );
	} );

	/**
	 * postIDが取得できない場合のテスト
	 */
	it( '[9C3C29BC] should throw an error when postID does not exist', () => {
		// ARRANGE
		( usePostIDFromDom as jest.Mock ).mockReturnValue( null );

		// ACT, ASSERT
		expect( () =>
			render(
				<PostIDProvider>
					<Sut />
				</PostIDProvider>
			)
		).toThrow( '[50F2A586]' );
	} );
} );
