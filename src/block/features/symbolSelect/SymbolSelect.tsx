import { BlockSelect, BlockSelectOption } from '../../components/BlockSelect';

interface SymbolSelectProps {
	value: string | null | undefined;
	symbols: string[] | null | undefined;
	onChange: ( symbol: string ) => void;
}
export const SymbolSelect: React.FC< SymbolSelectProps > = ( { value, symbols, onChange } ) => {
	const handleChange = ( event: React.ChangeEvent< HTMLSelectElement > ) => {
		onChange( event.target.value );
	};

	value = value ?? '';

	return (
		<BlockSelect value={ value } onChange={ handleChange }>
			{ value === '' ? <BlockSelectOption>{ 'Select a symbol' }</BlockSelectOption> : null }
			{ symbols?.map( ( symbol ) => (
				<BlockSelectOption key={ symbol } value={ symbol }>
					{ symbol }
				</BlockSelectOption>
			) ) }
		</BlockSelect>
	);
};
