import { useEffect, useMemo } from 'react';
import { PostSettingInput } from '../../../types/gql/generated';
import { useEditorProperty } from '../../provider/editor/useEditorProperty';
import { useSavePostSettingCallback } from '../../provider/postSetting/useSavePostSettingCallback';
import { ScreenPostSetting } from '../screenData/ScreenPostSetting.type';
import { useIsScreenDataChanged } from '../screenData/useIsScreenDataChanged';

/**
 * 投稿編集画面で、投稿が手動で保存された時に設定も保存します。
 * @param postSetting
 */
export const useAutoSavePostSetting = ( postSetting: ScreenPostSetting ) => {
	const isManualSaving = useIsManualSaving();
	const save = useSavePostSettingCallback();
	const isDataChanged = useIsScreenDataChanged( postSetting );

	useEffect( () => {
		if ( ! isManualSaving || ! isDataChanged ) {
			return;
		}

		save( convertToPostSettingInput( postSetting ) );
	}, [ postSetting, isManualSaving, save, isDataChanged ] );
};

const convertToPostSettingInput = ( postSetting: ScreenPostSetting ): PostSettingInput => {
	const sellingPrice = postSetting.sellingPrice;
	if ( sellingPrice === undefined || sellingPrice.symbol === undefined ) {
		throw new Error( '{416A72D9-62AC-478A-8E5B-985AD6062276}' );
	}

	return {
		sellingPrice: {
			amountHex: sellingPrice.amountHex,
			decimals: sellingPrice.decimals,
			symbol: sellingPrice.symbol,
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
