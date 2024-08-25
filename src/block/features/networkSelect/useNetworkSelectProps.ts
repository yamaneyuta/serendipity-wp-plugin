import { useCallback } from 'react';
import { NetworkType } from '../../../types/gql/generated';
import { useSelectedNetwork } from '../../provider/widgetState/selectedNetwork/useSelectedNetwork';
import { useSelectableNetworks } from './useSelectableNetworks';
import { useGetSellableSymbolsCallback } from '../../provider/serverData/useGetSellableSymbolsCallback';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';

/**
 * ネットワーク選択コンポーネントのプロパティを取得します。
 */
export const useNetworkSelectProps = () => {
	// 選択されたネットワークはProviderのstateから取得
	const { selectedNetwork: value } = useSelectedNetwork();

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
	const { setSelectedNetwork } = useSelectedNetwork();

	const { selectedPriceSymbol, setSelectedPriceSymbol } = useSelectedPriceSymbol();
	const getSellableSymbol = useGetSellableSymbolsCallback();

	return useCallback(
		( event: React.ChangeEvent< HTMLSelectElement > ) => {
			const network = event.target.value as NetworkType;
			// 選択されているネットワークを更新
			setSelectedNetwork( network );

			// 以下、現在選択されている通貨シンボルが変更後のネットワークに存在しない場合はnullを設定する処理
			// ネットワーク変更後に選択可能な通貨シンボルを取得
			const symbols = getSellableSymbol( network );
			if ( selectedPriceSymbol && symbols && ! symbols.includes( selectedPriceSymbol ) ) {
				// 選択されている通貨シンボルが変更後のネットワークで選択不可の場合はnullに変更
				setSelectedPriceSymbol( null );
			}
		},
		[ setSelectedNetwork, selectedPriceSymbol, setSelectedPriceSymbol, getSellableSymbol ]
	);
};
