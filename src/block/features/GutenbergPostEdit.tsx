import React, { Dispatch, SetStateAction, useCallback, useEffect, useState } from 'react';
import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { useScreenPostSetting } from './screenData/useScreenPostSetting';
import { ScreenPostSetting } from './screenData/ScreenPostSetting.type';
import { useAutoSavePostSetting } from './save/useAutoSavePostSetting';
import { useIsScreenDataChanged } from './screenData/useIsScreenDataChanged';
import { useSelectableSymbols } from './symbolSelect/useSelectableSymbols';
import { SymbolSelect } from './symbolSelect/SymbolSelect';
import { amountToInputValue, inputValueToAmount } from '@yamaneyuta/serendipity-lib-js-price-format';
import { BlockAmountInput } from '../components/BlockAmountInput';
import { NetworkSelect } from './networkSelect/NetworkSelect';
import { useSelectableNetworks } from './networkSelect/useSelectableNetworks';
import { useAutoBindServerData } from './load/useAutoBindServerData';
import { useSelectedNetwork } from '../provider/userInput/selectedNetwork/useSelectedNetwork';

type GutenbergPostEditProps = {
	onDataChanged: () => void;
};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( { onDataChanged } ) => {
	// サーバーから取得した情報を画面に反映
	useAutoBindServerData();

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

	// 画面上の情報が変更された時に保存ボタンが押せるようにonDataChangedを呼び出す
	const isDataChanged = useIsScreenDataChanged( postSetting );
	useEffect( () => {
		if ( isDataChanged ) {
			onDataChanged();
		}
	}, [ onDataChanged, isDataChanged ] );

	// 各種コントロールのプロパティを取得
	const networkSelectProps = useNetworkSelectProps( postSetting, setPostSetting );
	const priceValueProps = usePriceValueProps( postSetting, setPostSetting, serverPostSetting );
	const selectSymbolProps = useSymbolSelectProps( postSetting, setPostSetting );

	return (
		<Placeholder icon={ widget } label={ 'serendipity' }>
			<div style={ { width: '100%' } }>
				<NetworkSelect { ...networkSelectProps } />
			</div>
			<div style={ { display: 'flex', alignItems: 'flex-end' } }>
				<BlockAmountInput { ...priceValueProps } style={ { display: 'block', maxWidth: '100px' } } />
				<SymbolSelect { ...selectSymbolProps } />
			</div>
		</Placeholder>
	);
};

const useNetworkSelectProps = (
	postSetting: ScreenPostSetting,
	setPostSetting: Dispatch< SetStateAction< ScreenPostSetting > >
) => {
	const { selectedNetwork: value } = useSelectedNetwork();
	const networks = useSelectableNetworks();
	return {
		value,
		networks,
	};
};

/**
 * 通貨シンボル選択コントロールのプロパティを取得します。
 * @param postSetting
 * @param setPostSetting
 */
const useSymbolSelectProps = (
	postSetting: ScreenPostSetting,
	setPostSetting: Dispatch< SetStateAction< ScreenPostSetting > >
) => {
	const value = postSetting.sellingPrice?.symbol;
	const symbols = useSelectableSymbols( postSetting.sellingNetwork );
	const onChange = useSetPriceSymbolCallback( setPostSetting );
	return {
		value,
		symbols,
		onChange,
	};
};

const usePriceValueProps = (
	postSetting: ScreenPostSetting,
	setPostSetting: Dispatch< SetStateAction< ScreenPostSetting > >,
	serverPostSetting: ScreenPostSetting
): React.ComponentProps< typeof BlockAmountInput > => {
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
		width: 90,
	};
};

const useSetPriceSymbolCallback = ( setPostSetting: Dispatch< SetStateAction< ScreenPostSetting > > ) => {
	return useCallback(
		( symbol: string ) => {
			setPostSetting( ( s ) => {
				if ( ! s.sellingPrice ) {
					throw new Error( '{0EA4F3DD-42B0-4048-8C15-3D947D2405A3}' );
				}
				return {
					...s,
					sellingPrice: {
						...s.sellingPrice,
						symbol,
					},
				};
			} );
		},
		[ setPostSetting ]
	);
};
