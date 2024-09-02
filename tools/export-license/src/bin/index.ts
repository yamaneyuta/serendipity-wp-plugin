import { parseCommand } from '../lib/parseCommand';
import { getPackagesNpm } from '../lib/getPackagesNpm';
import { getPackagesComposer } from '../lib/getPackagesComposer';
import { getPackageManagerType, PackageManagerType } from '../lib/getPackageManagerType';
import { copyLicenseFiles } from '../lib/copyLicenseFiles';
import { exportLicenseMeta } from '../lib/exportLicenseMeta';
import { verifyMetaFile } from '../lib/verify/verifyMetaFile';

const main = async () => {
	// コマンドラインから引数を取得
	const { start, output, metaFile } = parseCommand();

	// 起点となるディレクトリから、パッケージマネージャーの種類を判定
	const packageManagerType = getPackageManagerType( start );

	// パッケージ情報を取得
	const packages = await getPackagesInfo( start, packageManagerType );

	// ライセンスファイルをコピー
	copyLicenseFiles( packages, start, output );

	// ライセンス情報を出力
	exportLicenseMeta( packages, start, output, metaFile );

	// 出力したライセンス情報を検証
	verifyMetaFile( metaFile );
};

const getPackagesInfo = async ( start: string, packageManagerType: PackageManagerType ) => {
	if ( packageManagerType.isNpm ) {
		return getPackagesNpm( {
			start,
			excludePrivatePackages: true, // プライベートパッケージは除外
			production: true, // devDependencies は除外
		} );
	} else if ( packageManagerType.isComposer ) {
		return getPackagesComposer( start );
	}
	throw new Error( `[E45C292D] Invalid argument. start: ${ start }` );
};

main();
