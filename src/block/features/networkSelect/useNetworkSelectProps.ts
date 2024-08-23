import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useSelectableNetworks } from './useSelectableNetworks';

export const useNetworkSelectProps = () => {
	// 選択されたネットワークはProviderのstateから取得
	const { selectedNetwork } = useSelectedNetwork();

	// 選択可能なネットワークはサーバーから受信した情報から取得される
	const networks = useSelectableNetworks();

	return {
		value: selectedNetwork,
		networks,
	};
};
