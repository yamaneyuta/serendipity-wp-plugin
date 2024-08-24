import { getByTestId, render } from '@testing-library/react';
import { useAutoBindServerData } from './useAutoBindServerData';
import { UserInputProvider } from '../../provider/userInput/UserInputProvider';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useInputPriceValue } from '../../provider/userInput/inputPriceValue/useInputPriceValue';
import { useSelectedPriceSymbol } from '../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol';
import { useSellingNetwork } from '../../provider/serverData/useSellingNetwork';
import { useSellingPriceValue } from '../../provider/serverData/useSellingPriceValue';
import { useSellingPriceSymbol } from '../../provider/serverData/useSellingPriceSymbol';
import { NetworkType } from '../../../types/gql/generated';

jest.mock( '../../provider/serverData/useSellingNetwork' );
jest.mock( '../../provider/serverData/useSellingPriceValue' );
jest.mock( '../../provider/serverData/useSellingPriceSymbol' );

const Sut: React.FC = () => {
	// サーバーから取得したデータをProviderのstateにバインド
	useAutoBindServerData();

	// Providerのstateを取得
	const selectedNetwork = useSelectedNetwork().selectedNetwork;
	const inputPriceValue = useInputPriceValue().inputPriceValue;
	const selectedPriceSymbol = useSelectedPriceSymbol().selectedPriceSymbol;

	return (
		<>
			<p data-testid="selectedNetwork">{ String( selectedNetwork ) }</p>
			<p data-testid="inputPriceValue">{ String( inputPriceValue ) }</p>
			<p data-testid="selectedPriceSymbol">{ String( selectedPriceSymbol ) }</p>
		</>
	);
};

describe( '[BA8935C1] useAutoBindServerData()', () => {
	const dataset = [
		// すべて値が取得できる
		[ NetworkType.Mainnet, '100', 'USD' ],
		// いずれかがnull
		[ null, '100', 'USD' ],
		[ NetworkType.Mainnet, null, 'USD' ],
		[ NetworkType.Mainnet, '100', null ],
		// いずれかがundefined
		[ undefined, '100', 'USD' ],
		[ NetworkType.Mainnet, undefined, 'USD' ],
		[ NetworkType.Mainnet, '100', undefined ],
		// すべてnull
		[ null, null, null ],
		// すべてundefined
		[ undefined, undefined, undefined ],
	];

	for ( const [ network, price, symbol ] of dataset ) {
		it( `should bind server data to provider state - network: ${ network }, price: ${ price }, symbol: ${ symbol }`, () => {
			// ARRANGE
			// サーバーから取得する値を設定
			( useSellingNetwork as jest.Mock ).mockReturnValue( network );
			( useSellingPriceValue as jest.Mock ).mockReturnValue( price );
			( useSellingPriceSymbol as jest.Mock ).mockReturnValue( symbol );

			// ACT
			render(
				<UserInputProvider>
					<Sut />
				</UserInputProvider>
			);

			// Providerのstateに値がバインドされていることを確認
			const get = ( id: string ) => getByTestId( document.documentElement, id ).textContent;
			expect( get( 'selectedNetwork' ) ).toBe( String( network ) );
			expect( get( 'inputPriceValue' ) ).toBe( String( price ) );
			expect( get( 'selectedPriceSymbol' ) ).toBe( String( symbol ) );
		} );
	}
} );
