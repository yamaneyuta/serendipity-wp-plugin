import { useCallback } from 'react';
import { NetworkType } from '../../../types/gql/generated';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useSelectableNetworks } from './useSelectableNetworks';

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

	return useCallback(
		( event: React.ChangeEvent< HTMLSelectElement > ) => {
			setSelectedNetwork( event.target.value as NetworkType );
		},
		[ setSelectedNetwork ]
	);
};
