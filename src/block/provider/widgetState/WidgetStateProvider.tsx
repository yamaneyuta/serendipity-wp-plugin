import { SelectedNetworkProvider } from './selectedNetwork/SelectedNetworkProvider';
import { InputPriceValueProvider } from './inputPriceValue/InputPriceValueProvider';
import { SelectedPriceSymbolProvider } from './selectedPriceSymbol/SelectedPriceSymbolProvider';

type WidgetStateProviderProps = {
	children: React.ReactNode;
};

export const WidgetStateProvider: React.FC< WidgetStateProviderProps > = ( { children } ) => {
	return (
		<SelectedNetworkProvider>
			<InputPriceValueProvider>
				<SelectedPriceSymbolProvider>{ children }</SelectedPriceSymbolProvider>
			</InputPriceValueProvider>
		</SelectedNetworkProvider>
	);
};
