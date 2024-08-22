import { NetworkType } from '../../../types/gql/generated';
import { BlockSelect, BlockSelectOption } from '../../components/BlockSelect';

interface NetworkSelectProps {
	value: string | null | undefined;
	networks: string[] | null | undefined;
	onChange: ( network: NetworkType ) => void;
}
export const NetworkSelect: React.FC< NetworkSelectProps > = ( { value, networks, onChange } ) => {
	const handleChange = ( event: React.ChangeEvent< HTMLSelectElement > ) => {
		onChange( event.target.value as NetworkType );
	};

	const disabled = value === undefined; // 読み込み中はコントロールを無効化

	return (
		<BlockSelect value={ value ?? '' } onChange={ handleChange } disabled={ disabled }>
			{ value === null ? <BlockSelectOption>{ 'Select a network' }</BlockSelectOption> : null }
			{ value === undefined ? <BlockSelectOption>{ 'Loading...' }</BlockSelectOption> : null }
			{ networks?.map( ( network ) => (
				<BlockSelectOption key={ network } value={ network }>
					{ network }
				</BlockSelectOption>
			) ) }
		</BlockSelect>
	);
};
