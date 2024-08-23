import { useEffect } from 'react';
import { usePostSetting } from '../../provider/serverData/postSetting/usePostSetting';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';

/**
 * サーバーから取得したデータをProviderのstateにバインドする機能を提供します。
 */
export const useAutoBindServerData = () => {
	useAutoBindSellingNetwork(); // 販売ネットワークの情報をバインド
};

/**
 * サーバーから受信した販売ネットワークの情報をProviderのstateにバインドします。
 */
const useAutoBindSellingNetwork = () => {
	// サーバーから設定を取得
	const postSetting = usePostSetting();

	// 画面で選択済みのネットワーク情報を設定する関数を取得
	const { setSelectedNetwork } = useSelectedNetwork();

	// サーバーから受信した値が変更された時に販売ネットワークを設定する
	useEffect( () => {
		const selectedNetwork = postSetting === undefined ? undefined : postSetting.sellingNetwork;
		setSelectedNetwork( selectedNetwork );
	}, [ postSetting ] );
};
