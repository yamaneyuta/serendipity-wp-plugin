import path from 'path';
import * as checker from 'license-checker';
import { exportLicense } from './export-license';

const EXPORT_DIR = path.join( process.cwd(), 'public', 'license', 'block' );

/**
 * ブロックエディタが依存しているライブラリのライセンスを出力します。
 */
const main = () => {
	checker.init(
		{
			start: process.cwd(),
			excludePrivatePackages: true, // プライベートパッケージは除外
			production: true, // devDependencies は除外
		},
		( err, packages ) => {
			exportLicense( packages, `${ process.cwd() }/node_modules`, EXPORT_DIR );
		}
	);
};

main();
