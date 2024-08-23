import { useCallback } from 'react';
import { useSelectedPriceSymbol } from '../../provider/userInput/selectedPriceSymbol/useSelectedPriceSymbol';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';
import { useSelectableSymbols } from './useSelectableSymbols';

/**
 * 通貨シンボル選択コントロールのプロパティを取得します。
 */
export const useSymbolSelectProps = () => {
	const value = useSelectedPriceSymbol().selectedPriceSymbol;
	const symbols = useSelectableSymbols( useSelectedNetwork().selectedNetwork );
	const onChange = useOnChangeCallback();
	return {
		value,
		symbols,
		onChange,
	};
};

const useOnChangeCallback = () => {
	const { setSelectedPriceSymbol } = useSelectedPriceSymbol();

	return useCallback(
		( event: React.ChangeEvent< HTMLSelectElement > ) => {
			setSelectedPriceSymbol( event.target.value );
		},
		[ setSelectedPriceSymbol ]
	);
};
