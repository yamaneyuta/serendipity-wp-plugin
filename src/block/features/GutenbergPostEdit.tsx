import React, { Dispatch, SetStateAction, useCallback, useEffect, useState } from 'react';
import { useScreenPostSetting } from './screenData/useScreenPostSetting';
import { ScreenPostSetting } from './screenData/ScreenPostSetting.type';
import { useAutoSavePostSetting } from './save/useAutoSavePostSetting';
import { useIsScreenDataChanged } from './screenData/useIsScreenDataChanged';
import { useSellableSymbols } from './screenData/useSellableSymbols';
import { SymbolSelect } from './SymbolSelect';
import { amountToInputValue, inputValueToAmount } from '@yamaneyuta/serendipity-lib-js-price-format';
import { BlockAmountInput } from '../components/BlockAmountInput';

type GutenbergPostEditProps = {
	onDataChanged: () => void;
};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( { onDataChanged } ) => {
	// 画面で保持する設定情報
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
	const priceValueProps = usePriceValueProps( postSetting, setPostSetting, serverPostSetting );
	const selectSymbolProps = useSymbolSelectProps( postSetting, setPostSetting );

	return (
		<>
			<h2>GutenbergPostEdit</h2>

			{ /* <button onClick={ onClick }>set price</button> */ }
			<div>
				amount: { JSON.stringify( postSetting.sellingPrice?.amountHex ) } <br />
				decimals: { JSON.stringify( postSetting.sellingPrice?.decimals ) } <br />
				symbol: { JSON.stringify( postSetting.sellingPrice?.symbol ) } <br />
			</div>
			<div>
				<BlockAmountInput { ...priceValueProps } />
				<SymbolSelect { ...selectSymbolProps } />
			</div>
		</>
	);
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
	const symbols = useSellableSymbols();
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
