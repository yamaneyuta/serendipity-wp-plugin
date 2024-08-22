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

	const disabled = value === undefined; // 読み込み中はコントロールを無効化

	return (
		<BlockSelect value={ value ?? '' } onChange={ handleChange } disabled={ disabled }>
			{ value === null ? <BlockSelectOption>{ 'Select a symbol' }</BlockSelectOption> : null }
			{ value === undefined ? <BlockSelectOption>{ 'Loading...' }</BlockSelectOption> : null }
			{ symbols?.map( ( symbol ) => (
				<BlockSelectOption key={ symbol } value={ symbol }>
					{ symbol }
				</BlockSelectOption>
			) ) }
		</BlockSelect>
	);
};
