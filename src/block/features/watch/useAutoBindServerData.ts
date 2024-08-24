import { useEffect } from 'react';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useInputPriceValue } from '../../provider/userInput/inputPriceValue/useInputPriceValue';
import { useSelectedPriceSymbol } from '../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol';
import { useSellingNetwork } from '../../provider/serverData/useSellingNetwork';
import { useSellingPriceValue } from '../../provider/serverData/useSellingPriceValue';
import { useSellingPriceSymbol } from '../../provider/serverData/useSellingPriceSymbol';

/**
 * サーバーから取得したデータをProviderのstateにバインドする機能を提供します。
 */
export const useAutoBindServerData = () => {
	useAutoBindSellingNetwork(); // 販売ネットワークの情報をバインド
	useAutoBindSellingPriceValue(); // 販売価格の情報をバインド
	useAutoBindSelectedPriceSymbol(); // 販売価格の通貨シンボルの情報をバインド
};

/**
 * サーバーから受信した販売ネットワークの情報をProviderのstateにバインドします。
 */
const useAutoBindSellingNetwork = () => {
	// サーバーから販売ネットワーク設定を取得
	const sellingNetwork = useSellingNetwork();

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
	const sellingPriceValue = useSellingPriceValue();

	// 画面で入力された価格を設定する関数を取得
	const { setInputPriceValue } = useInputPriceValue();

	// サーバーから受信した値が変更された時に販売価格を設定する
	useEffect( () => {
		setInputPriceValue( sellingPriceValue );
	}, [ sellingPriceValue, setInputPriceValue ] );
};

/**
 * サーバーから受信した販売価格の通貨シンボルの情報をProviderのstateにバインドします。
 */
export const useAutoBindSelectedPriceSymbol = () => {
	// 販売価格の通貨シンボルをサーバーから取得
	const sellingSymbol = useSellingPriceSymbol();

	// 画面で選択された通貨シンボルを設定する関数を取得
	const { setSelectedPriceSymbol } = useSelectedPriceSymbol();

	// サーバーから受信した値が変更された時に通貨シンボルを設定する
	useEffect( () => {
		setSelectedPriceSymbol( sellingSymbol );
	}, [ sellingSymbol, setSelectedPriceSymbol ] );
};
