import { useEffect } from 'react';
import { usePostSetting } from '../../provider/serverData/postSetting/usePostSetting';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useInputPriceValue } from '../../provider/userInput/inputPriceValue/useInputPriceValue';

/**
 * サーバーから取得したデータをProviderのstateにバインドする機能を提供します。
 */
export const useAutoBindServerData = () => {
	useAutoBindSellingNetwork(); // 販売ネットワークの情報をバインド
	useAutoBindSellingPriceValue(); // 販売価格の情報をバインド
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
const useAutoBindSellingPriceValue = () => {
	// 販売価格をサーバーから取得
	const sellingPrice = usePostSetting()?.sellingPrice;

	// 画面で入力された価格を設定する関数を取得
	const { setInputPriceValue } = useInputPriceValue();

	// サーバーから受信した値が変更された時に販売価格を設定する
	useEffect( () => {
		const amountHex = sellingPrice ? sellingPrice.amountHex : sellingPrice;
		const decimals = sellingPrice ? sellingPrice.decimals : sellingPrice;
		setInputPriceValue( amountHex, decimals );
	}, [ sellingPrice, setInputPriceValue ] );
};
