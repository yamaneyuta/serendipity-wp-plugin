import { useCallback } from 'react';
import { useSelectedNetworkCategory } from '../../provider/widgetState/selectedNetwork/useSelectedNetworkCategory';
import { useSelectableNetworks } from './useSelectableNetworks';
import { useGetSellableSymbolsCallback } from '../../provider/serverData/useGetSellableSymbolsCallback';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';
import { NetworkCategory } from '../../../types/NetworkCategory';

/**
 * ネットワーク選択コンポーネントのプロパティを取得します。
 */
export const useNetworkCategorySelectProps = () => {
	// 選択されたネットワークはProviderのstateから取得
	const { selectedNetworkCategory: value } = useSelectedNetworkCategory();

	// 選択可能なネットワークはサーバーから受信した情報から取得される
	const networks = useSelectableNetworks();

	// ネットワークが変更された時のコールバック
	const onChange = useOnChangeCallback();

	// 読み込み中はコントロールを無効化
	const disabled = value === undefined;

	return {
		value,
		networks,
		onChange,
		disabled,
	};
};

/**
 * ネットワークが変更された時のコールバックを取得します。
 */
const useOnChangeCallback = () => {
	const { setSelectedNetworkCategory } = useSelectedNetworkCategory();

	const { selectedPriceSymbol, setSelectedPriceSymbol } = useSelectedPriceSymbol();
	const getSellableSymbol = useGetSellableSymbolsCallback();

	return useCallback(
		( event: React.ChangeEvent< HTMLSelectElement > ) => {
			const network = NetworkCategory.from( parseInt( event.target.value ) );
			// 選択されているネットワークを更新
			setSelectedNetworkCategory( network );

			// 以下、現在選択されている通貨シンボルが変更後のネットワークに存在しない場合はnullを設定する処理
			// ネットワーク変更後に選択可能な通貨シンボルを取得
			const symbols = getSellableSymbol( network );
			if ( selectedPriceSymbol && symbols && ! symbols.includes( selectedPriceSymbol ) ) {
				// 選択されている通貨シンボルが変更後のネットワークで選択不可の場合はnullに変更
				setSelectedPriceSymbol( null );
			}
		},
		[ setSelectedNetworkCategory, selectedPriceSymbol, setSelectedPriceSymbol, getSellableSymbol ]
	);
};
