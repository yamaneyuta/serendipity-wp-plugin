import { SelectedNetworkProvider } from './selectedNetwork/SelectedNetworkProvider';
import { InputPriceAmountProvider } from './inputPriceAmount/InputPriceAmountProvider';
import { SelectedPriceSymbolProvider } from './selectedPriceSymbol/SelectedPriceSymbolProvider';

type UserInputProviderProps = {
	children: React.ReactNode;
};

export const UserInputProvider: React.FC< UserInputProviderProps > = ( { children } ) => {
	return (
		<SelectedNetworkProvider>
			<InputPriceAmountProvider>
				<SelectedPriceSymbolProvider>{ children }</SelectedPriceSymbolProvider>
			</InputPriceAmountProvider>
		</SelectedNetworkProvider>
	);
};
