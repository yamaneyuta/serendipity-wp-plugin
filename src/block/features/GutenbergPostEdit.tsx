import { useCallback, useEffect, useState } from 'react';
import { useScreenPostSetting } from './screenData/useScreenPostSetting';
import { ScreenPostSetting } from './screenData/ScreenPostSetting.type';
import { useAutoSavePostSetting } from './save/useAutoSavePostSetting';
import { useIsScreenDataChanged } from './screenData/useIsScreenDataChanged';

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

	// 画面の情報が変更された時に呼び出されるコールバック関数
	const setPriceValue = useSetPriceValueCallback( setPostSetting );
	const setPriceSymbol = useSetPriceSymbolCallback( setPostSetting );

	// debug
	const onClick = async () => {
		setPriceValue( '0x1234567890abcdef', postSetting.sellingPrice!.decimals + 1 );
		setPriceSymbol( 'ETH' );
	};

	return (
		<>
			<h2>GutenbergPostEdit</h2>

			<button onClick={ onClick }>set price</button>
			<div>
				amount: { JSON.stringify( postSetting.sellingPrice?.amountHex ) } <br />
				decimals: { JSON.stringify( postSetting.sellingPrice?.decimals ) } <br />
				symbol: { JSON.stringify( postSetting.sellingPrice?.symbol ) } <br />
			</div>
		</>
	);
};

const useSetPriceValueCallback = ( setPostSetting: React.Dispatch< React.SetStateAction< ScreenPostSetting > > ) => {
	return useCallback(
		( amountHex: string, decimals: number ) => {
			setPostSetting( ( s ) => ( {
				...s,
				sellingPrice: {
					amountHex,
					decimals,
				},
			} ) );
		},
		[ setPostSetting ]
	);
};

const useSetPriceSymbolCallback = ( setPostSetting: React.Dispatch< React.SetStateAction< ScreenPostSetting > > ) => {
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
