import React, { useEffect, useState } from 'react';
import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { useScreenPostSetting } from './screenData/useScreenPostSetting';
import { ScreenPostSetting } from './screenData/ScreenPostSetting.type';
import { useAutoSavePostSetting } from './watch/useAutoSavePostSetting';
import { SymbolSelect } from './symbolSelect/SymbolSelect';
import { NetworkSelect } from './networkSelect/NetworkSelect';
import { useAutoBindServerData } from './watch/useAutoBindServerData';
import { useNetworkSelectProps } from './networkSelect/useNetworkSelectProps';
import { useNotifyDataChangedToEditor } from './watch/useNotifyDataChangedToEditor';
import { useSymbolSelectProps } from './symbolSelect/useSymbolSelectProps';
import { usePriceValueInputProps } from './priceValueInput/usePriceValueInputProps';
import { PriceValueInput } from './priceValueInput/PriceValueInput';

type GutenbergPostEditProps = {
	onDataChanged: () => void;
};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( { onDataChanged } ) => {
	// サーバーから取得した情報を画面に反映
	useAutoBindServerData();

	// データが変更された時にWordPressのエディタに通知
	useNotifyDataChangedToEditor( onDataChanged );

	// 画面で保持する設定情報
	/** @deprecated */
	const [ postSetting, setPostSetting ] = useState< ScreenPostSetting >( {} );
	// サーバーから取得した設定情報
	const serverPostSetting = useScreenPostSetting();
	// 投稿保存時に設定情報を保存する
	useAutoSavePostSetting( postSetting );

	// サーバーから取得した設定情報を画面で保持する設定情報にコピー
	useEffect( () => {
		setPostSetting( JSON.parse( JSON.stringify( serverPostSetting ) ) );
	}, [ serverPostSetting ] );

	return (
		<Placeholder icon={ widget } label={ 'serendipity' }>
			<div style={ { width: '100%' } }>
				<NetworkSelect { ...useNetworkSelectProps() } />
			</div>
			<div style={ { display: 'flex', alignItems: 'flex-end' } }>
				<PriceValueInput
					{ ...usePriceValueInputProps() }
					width={ 90 }
					style={ { display: 'block', maxWidth: '100px' } }
				/>
				<SymbolSelect { ...useSymbolSelectProps() } />
			</div>
		</Placeholder>
	);
};
