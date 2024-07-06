import { BlockSelect, BlockSelectOption } from '../components/BlockSelect';

interface SymbolSelectProps {
	value: string | undefined;
	symbols: string[] | undefined;
	onChange: ( symbol: string ) => void;
}
export const SymbolSelect: React.FC< SymbolSelectProps > = ( { value, symbols, onChange } ) => {
	const handleChange = ( event: React.ChangeEvent< HTMLSelectElement > ) => {
		onChange( event.target.value );
	};

	return (
		<BlockSelect value={ value } onChange={ handleChange }>
			{ symbols?.map( ( symbol ) => (
				<BlockSelectOption key={ symbol } value={ symbol }>
					{ symbol }
				</BlockSelectOption>
			) ) }
		</BlockSelect>
	);
};
