import { useIsSellingNetworkChanged } from './useIsSellingNetworkChanged';
import { useSelectedNetwork } from '../../../provider/widgetState/selectedNetwork/useSelectedNetwork';
import { useSellingNetwork } from '../../../provider/serverData/useSellingNetwork';
import { NetworkType } from '../../../../types/gql/generated';

jest.mock( '../../../provider/widgetState/selectedNetwork/useSelectedNetwork' );
jest.mock( '../../../provider/serverData/useSellingNetwork' );

describe( '[F183C408] useIsSellingNetworkChanged()', () => {
	// undefinedが含まれる場合は変更されたとみなさない
	// [サーバーから取得した販売ネットワーク, ユーザーが選択したネットワーク, 期待値(変更されたかどうか)]
	const dataset: [ NetworkType | null | undefined, NetworkType | null | undefined, boolean ][] = [
		[ NetworkType.Mainnet, NetworkType.Mainnet, false ],
		[ NetworkType.Mainnet, NetworkType.Testnet, true ],
		[ NetworkType.Mainnet, null, true ],
		[ NetworkType.Mainnet, undefined, false ],
		[ NetworkType.Testnet, NetworkType.Mainnet, true ],
		[ NetworkType.Testnet, NetworkType.Testnet, false ],
		[ NetworkType.Testnet, null, true ],
		[ NetworkType.Testnet, undefined, false ],
		[ null, NetworkType.Mainnet, true ],
		[ null, NetworkType.Testnet, true ],
		[ null, null, false ],
		[ null, undefined, false ],
		[ undefined, NetworkType.Mainnet, false ],
		[ undefined, NetworkType.Testnet, false ],
		[ undefined, null, false ],
		[ undefined, undefined, false ],
	];

	for ( const [ srv, usr, expected ] of dataset ) {
		it( `[E45D770B] useIsSellingNetworkChanged() - srv: (${ srv }, usr: ${ usr }) -> ${ expected }`, async () => {
			( useSellingNetwork as jest.Mock ).mockReturnValue( srv );
			( useSelectedNetwork as jest.Mock ).mockReturnValue( { selectedNetwork: usr } );

			const isSellingNetworkChanged = useIsSellingNetworkChanged();
			expect( isSellingNetworkChanged ).toEqual( expected );
		} );
	}
} );
