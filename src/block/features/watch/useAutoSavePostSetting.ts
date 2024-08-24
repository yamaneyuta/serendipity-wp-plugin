import { useEffect, useMemo } from 'react';
import AsyncLock from 'async-lock';
import { inputValueToAmount } from '@yamaneyuta/serendipity-lib-js-price-format';
import { PostSettingInput } from '../../../types/gql/generated';
import { useEditorProperty } from '../../provider/editor/useEditorProperty';
import { useSavePostSettingCallback } from '../../provider/serverData/postSetting/useSavePostSettingCallback';
import { useIsDataChanged } from './useIsDataChanged';
import { useInputPriceValue } from '../../provider/userInput/inputPriceValue/useInputPriceValue';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useSelectedPriceSymbol } from '../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol';

/**
 * 投稿編集画面で、投稿が手動で保存された時に設定も保存します。
 */
export const useAutoSavePostSetting = () => {
	const lock = useMemo( () => new AsyncLock(), [] );
	const isManualSaving = useIsManualSaving();
	const isDataChanged = useIsDataChanged();
	const save = useSavePostSettingCallback();
	const postSettingInput = usePostSettingInput();

	useEffect( () => {
		if ( ! isManualSaving || ! isDataChanged ) {
			return;
		}

		if ( ! postSettingInput ) {
			throw new Error( '[136AA840] Invalid postSettingInput' );
		}

		if ( ! lock.isBusy() ) {
			lock.acquire( '{77F905B2-764C-409C-B8D9-F3D757D8A790}', async () => {
				await save( postSettingInput );
			} );
		}
	}, [ lock, isManualSaving, isDataChanged, save, postSettingInput ] );
};

/**
 * サーバーへ登録するための値を取得します。
 */
const usePostSettingInput = (): PostSettingInput | null | undefined => {
	// ユーザーが選択したネットワーク種別
	const { selectedNetwork } = useSelectedNetwork();
	// ユーザーが入力した価格の値
	const { inputPriceValue } = useInputPriceValue();
	// ユーザーが選択した価格の通貨シンボル
	const { selectedPriceSymbol } = useSelectedPriceSymbol();

	// 読み込み中の値がある場合はundefinedを返す
	if ( selectedNetwork === undefined || inputPriceValue === undefined || selectedPriceSymbol === undefined ) {
		return undefined;
	}
	// いずれかの値がnullである場合はnullを返す(サーバー側で登録できないためあらかじめフロントエンドでnullとして扱う)
	if ( selectedNetwork === null || inputPriceValue === null || selectedPriceSymbol === null ) {
		return null;
	}

	const { amount, decimals } = inputValueToAmount( inputPriceValue );
	return {
		sellingNetwork: selectedNetwork,
		sellingPrice: {
			amountHex: '0x' + amount.toString( 16 ),
			decimals,
			symbol: selectedPriceSymbol,
		},
	};
};

/**
 * 投稿編集を手動で保存中かどうかを取得します。
 */
const useIsManualSaving = () => {
	const { isSaving, isAutosavingPost } = useEditorProperty();
	return useMemo( () => {
		// 保存中かつ自動保存中でない時、手動保存中と判断
		return isSaving && ! isAutosavingPost;
	}, [ isSaving, isAutosavingPost ] );
};
