import { SelectedNetworkProvider } from './selectedNetwork/SelectedNetworkProvider';
import { InputPriceValueProvider } from './inputPriceValue/InputPriceValueProvider';
import { SelectedPriceSymbolProvider } from './selectedPriceSymbol/SelectedPriceSymbolProvider';

type UserInputProviderProps = {
	children: React.ReactNode;
};

export const UserInputProvider: React.FC< UserInputProviderProps > = ( { children } ) => {
	return (
		<SelectedNetworkProvider>
			<InputPriceValueProvider>
				<SelectedPriceSymbolProvider>{ children }</SelectedPriceSymbolProvider>
			</InputPriceValueProvider>
		</SelectedNetworkProvider>
	);
};
