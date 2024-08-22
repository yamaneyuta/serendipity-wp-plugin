import { NetworkType } from '../../../types/gql/generated';
import { useSelectableSymbols as sut } from './useSelectableSymbols';
import { usePostSetting } from '../../provider/postSetting/usePostSetting';
import { renderHook } from '../../../../jest-lib/renderHook';

jest.mock( '../../provider/postSetting/usePostSetting' );

type UsePostSettingResult = ReturnType< typeof usePostSetting >;

/**
 * 通常のテスト。各ネットワークで選択可能な通貨シンボルを取得できる場合。
 */
it( '[23C5844D] useSelectableSymbols() - default', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT
	const mainnetSellableSymbols = renderHook( () => sut( NetworkType.Mainnet ) ).result.current;
	const testnetSellableSymbols = renderHook( () => sut( NetworkType.Testnet ) ).result.current;
	const privatenetSellableSymbols = renderHook( () => sut( NetworkType.Privatenet ) ).result.current;

	// ASSERT
	expect( mainnetSellableSymbols ).toEqual( [ 'JPY' ] );
	expect( testnetSellableSymbols ).toEqual( [ 'USD' ] );
	expect( privatenetSellableSymbols ).toEqual( [ 'EUR', 'GBP' ] );
} );

/**
 * 投稿設定をサーバーから取得している最中のテスト。
 */
it( '[1DDC9FA6] useSelectableSymbols(undefined) - loading', async () => {
	// ARRANGE
	const res: UsePostSettingResult = undefined;
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT
	// 仕様上、ネットワーク種別にundefinedが渡される時は読み込み中の時。
	const sellableSymbols = renderHook( () => sut( undefined ) ).result.current;

	// ASSERT
	expect( sellableSymbols ).toBeUndefined();
} );

/**
 * 販売ネットワークが未指定の場合のテスト
 */
it( '[1DDC9FA6] useSelectableSymbols(null) - loading', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT
	const sellableSymbols = renderHook( () => sut( null ) ).result.current;

	// ASSERT
	expect( sellableSymbols ).toBeNull();
} );

/**
 * 投稿設定をサーバーから取得している最中に不正なネットワーク種別が指定された場合のテスト。
 */
it( '[1DDC9FA6] useSelectableSymbols() - loading, invalid network type', async () => {
	// ARRANGE
	const res: UsePostSettingResult = undefined;
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT, ASSERT
	// データ取得中はネットワーク種別はundefinedを渡すべきだが、不正な値が渡された時のテスト。
	expect( () => renderHook( () => sut( NetworkType.Mainnet ) ) ).toThrow( '[FC51AFA9]' );
} );

/**
 * 選択可能な通貨シンボルが取得できなかった場合のテスト。
 */
it( '[E9CD00AF] useSelectableSymbols() - null', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: null,
		testnetSellableSymbols: null,
		privatenetSellableSymbols: null,
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT
	const mainnetSellableSymbols = renderHook( () => sut( NetworkType.Mainnet ) ).result.current;
	const testnetSellableSymbols = renderHook( () => sut( NetworkType.Testnet ) ).result.current;
	const privatenetSellableSymbols = renderHook( () => sut( NetworkType.Privatenet ) ).result.current;

	// ASSERT
	expect( mainnetSellableSymbols ).toBeNull();
	expect( testnetSellableSymbols ).toBeNull();
	expect( privatenetSellableSymbols ).toBeNull();
} );

/**
 * 無効なネットワーク種別が指定された場合のテスト。
 */
it( '[BF4948EE] useSelectableSymbols() - invalid network type', async () => {
	// ARRANGE
	const res: UsePostSettingResult = {
		mainnetSellableSymbols: [ 'JPY' ],
		testnetSellableSymbols: [ 'USD' ],
		privatenetSellableSymbols: [ 'EUR', 'GBP' ],
		sellingNetwork: null,
		sellingPrice: null,
	};
	( usePostSetting as jest.Mock ).mockReturnValue( res );

	// ACT, ASSERT
	expect( () => renderHook( () => sut( 'INVALID_NETWORK' as unknown as NetworkType ) ) ).toThrow( '[3D102039]' );
} );
