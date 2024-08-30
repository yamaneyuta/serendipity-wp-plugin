import path from 'node:path';
import { exportLicense } from './lib/exportLicense';
import { getComposerPackages } from './lib/getComposerPackages';

const EXPORT_DIR = path.join( process.cwd(), 'public', 'license', 'php' );

/**
 * プラグイン(includesディレクトリ)が依存しているPHPライブラリのライセンスを出力します。
 */
const main = () => {
	const projectPath = path.join( process.cwd(), 'includes' );
	const vendorRootPath = path.join( projectPath, 'vendor' );

	// PHPの依存ライブラリをcomposerを使って使用
	const packages = getComposerPackages( projectPath );
	// 依存ライブラリのライセンスを出力
	exportLicense( packages, vendorRootPath, EXPORT_DIR );
};

main();
