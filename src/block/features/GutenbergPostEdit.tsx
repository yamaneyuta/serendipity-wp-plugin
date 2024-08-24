import React, { Dispatch, SetStateAction, useCallback, useEffect, useState } from 'react';
import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { useScreenPostSetting } from './screenData/useScreenPostSetting';
import { ScreenPostSetting } from './screenData/ScreenPostSetting.type';
import { useAutoSavePostSetting } from './watch/useAutoSavePostSetting';
import { SymbolSelect } from './symbolSelect/SymbolSelect';
import { amountToInputValue, inputValueToAmount } from '@yamaneyuta/serendipity-lib-js-price-format';
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

const usePriceValueProps = (
	postSetting: ScreenPostSetting,
	setPostSetting: Dispatch< SetStateAction< ScreenPostSetting > >,
	serverPostSetting: ScreenPostSetting
): React.ComponentProps< typeof PriceValueInput > => {
	const [ text, setText ] = useState< string | undefined >( undefined ); // 入力テキストボックスに表示する値

	// サーバーから取得した設定情報が変更された時に入力テキストボックスに表示する値を更新
	useEffect( () => {
		if ( ! serverPostSetting.sellingPrice ) {
			setText( undefined );
			return;
		}
		const amount = BigInt( serverPostSetting.sellingPrice.amountHex );
		const decimals = serverPostSetting.sellingPrice.decimals;
		setText( amountToInputValue( amount, decimals ) );
	}, [ serverPostSetting ] );

	// ユーザーによって入力値が変更された時の処理
	const onChange = ( e: React.ChangeEvent< HTMLInputElement > ) => {
		const inputText = e.target.value;
		setText( inputText );
		const { amount, decimals } = inputValueToAmount( inputText );
		setPostSetting( ( s ) => ( {
			...s,
			sellingPrice: {
				...s.sellingPrice,
				amountHex: '0x' + amount.toString( 16 ),
				decimals,
			},
		} ) );
	};

	return {
		value: text ?? '',
		onChange,
	};
};
