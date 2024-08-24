import { ChangeEventHandler } from 'react';
import { useInputPriceValue } from '../../provider/userInput/inputPriceValue/useInputPriceValue';

/**
 * 価格入力コントロールのプロパティを取得します。
 */
export const usePriceValueInputProps = () => {
	const { inputPriceValue: value } = useInputPriceValue();

	const onChange = useOnChangeCallback();

	return {
		value: value ?? '', // nullやundefinedの場合は空文字を表示
		onChange,
	};
};

/**
 * 価格の入力値が変更された時のコールバックを取得します。
 */
const useOnChangeCallback = (): ChangeEventHandler< HTMLInputElement > => {
	const { setInputPriceValue } = useInputPriceValue();

	return ( event: React.ChangeEvent< HTMLInputElement > ) => {
		const value = event.target.value;
		setInputPriceValue( value === '' ? null : value ); // 空文字の場合はnullに変換して保持
	};
};
