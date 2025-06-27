import { useMemo } from 'react';
import { BlockInput } from './BlockInput';
import { IDataTestIdProps } from './IDataTestIdProps';

/**
 * BlockAmountInputコンポーネントのpropsを定義
 *
 * BlockInputのpropsを継承した型を作成。`type`等のプロパティは固定値を設定するため、BlockAmountInputPropsには含めない。
 */
export interface BlockAmountInputProps
	extends IDataTestIdProps,
		Omit< React.InputHTMLAttributes< HTMLInputElement >, 'type' | 'min' > {}

/**
 * 数量、小数点以下桁数を元に数値を表示するコンポーネント
 * @param props
 */
export const BlockAmountInput: React.FC< BlockAmountInputProps > = ( props ) => {
	return <BlockInput { ...useAmountInputProps( props ) } />;
};

const useAmountInputProps = ( props: React.ComponentProps< 'input' > ) => {
	return useMemo(
		() => ( {
			...props,
			type: 'number',
			min: 0,
			onKeyDown: ( e: React.KeyboardEvent< HTMLInputElement > ) => {
				if ( [ '-', '+', 'e' ].includes( e.key ) ) {
					e.preventDefault();
				} else {
					props.onKeyDown?.( e );
				}
			},
		} ),
		[ props ]
	);
};
