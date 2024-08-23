import { useEffect } from 'react';
import { usePostSetting } from '../../provider/serverData/postSetting/usePostSetting';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useInputPriceAmount } from '../../provider/userInput/inputPriceAmount/useInputPriceAmount';

/**
 * サーバーから取得したデータをProviderのstateにバインドする機能を提供します。
 */
export const useAutoBindServerData = () => {
	useAutoBindSellingNetwork(); // 販売ネットワークの情報をバインド
	useAutoBindSellingPriceAmount(); // 販売価格の情報をバインド
};

/**
 * サーバーから受信した販売ネットワークの情報をProviderのstateにバインドします。
 */
const useAutoBindSellingNetwork = () => {
	// サーバーから販売ネットワーク設定を取得
	const sellingNetwork = usePostSetting()?.sellingNetwork;

	// 画面で選択済みのネットワーク情報を設定する関数を取得
	const { setSelectedNetwork } = useSelectedNetwork();

	// サーバーから受信した値が変更された時に販売ネットワークを設定する
	useEffect( () => {
		setSelectedNetwork( sellingNetwork );
	}, [ sellingNetwork, setSelectedNetwork ] );
};

/**
 * サーバーから受信した販売価格の情報をProviderのstateにバインドします。
 */
const useAutoBindSellingPriceAmount = () => {
	// 販売価格をサーバーから取得
	const sellingPrice = usePostSetting()?.sellingPrice;

	// 画面で入力された価格を設定する関数を取得
	const { setInputPriceAmount } = useInputPriceAmount();

	// サーバーから受信した値が変更された時に販売価格を設定する
	useEffect( () => {
		const amountHex = sellingPrice ? sellingPrice.amountHex : sellingPrice;
		const decimals = sellingPrice ? sellingPrice.decimals : sellingPrice;
		setInputPriceAmount( amountHex, decimals );
	}, [ sellingPrice, setInputPriceAmount ] );
};
