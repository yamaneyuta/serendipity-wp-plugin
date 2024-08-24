import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { useAutoSavePostSetting } from './watch/useAutoSavePostSetting';
import { SymbolSelect } from './symbolSelect/SymbolSelect';
import { NetworkSelect } from './networkSelect/NetworkSelect';
import { useAutoBindServerData } from './watch/useAutoBindServerData';
import { useNetworkSelectProps } from './networkSelect/useNetworkSelectProps';
import { useNotifyDataChangedToEditor } from './watch/useNotifyDataChangedToEditor';
import { useSymbolSelectProps } from './symbolSelect/useSymbolSelectProps';
import { usePriceValueInputProps } from './priceValueInput/usePriceValueInputProps';
import { PriceValueInput } from './priceValueInput/PriceValueInput';

type GutenbergPostEditProps = {
	onDataChanged: () => void;
};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( { onDataChanged } ) => {
	// サーバーから取得した情報を画面に反映
	useAutoBindServerData();
	// データが変更された時にWordPressのエディタに通知
	useNotifyDataChangedToEditor( onDataChanged );
	// 投稿保存時に設定情報を保存
	useAutoSavePostSetting();

	return (
		<Placeholder icon={ widget } label={ 'serendipity' }>
			<div style={ { width: '100%' } }>
				<NetworkSelect { ...useNetworkSelectProps() } />
			</div>
			<div style={ { display: 'flex', alignItems: 'flex-end' } }>
				<PriceValueInput
					{ ...usePriceValueInputProps() }
					width={ 90 }
					style={ { display: 'block', maxWidth: '100px' } }
				/>
				<SymbolSelect { ...useSymbolSelectProps() } />
			</div>
		</Placeholder>
	);
};
