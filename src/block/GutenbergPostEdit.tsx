import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { useAutoSavePostSetting } from './features/watch/useAutoSavePostSetting';
import { SymbolSelect } from './features/symbolSelect/SymbolSelect';
import { NetworkSelect } from './features/networkSelect/NetworkSelect';
import { useAutoBindServerData } from './features/watch/useAutoBindServerData';
import { useNetworkSelectProps } from './features/networkSelect/useNetworkSelectProps';
import { useNotifyDataChangedToEditor } from './features/watch/useNotifyDataChangedToEditor';
import { useSymbolSelectProps } from './features/symbolSelect/useSymbolSelectProps';
import { usePriceValueInputProps } from './features/priceValueInput/usePriceValueInputProps';
import { PriceValueInput } from './features/priceValueInput/PriceValueInput';

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
