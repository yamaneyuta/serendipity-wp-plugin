#!/bin/bash

# プロジェクトルートで実行されていることを確認
# カレントディレクトリに`package.json`が存在すれば、プロジェクトルートとみなす
if [ ! -f ./package.json ]; then
	echo "This script must be run in the root of the project" 1>&2
	exit 1
fi

function main() {
	# カレントユーザーを`wp-env`コマンド実行ユーザーとして指定するために`docker`グループに追加。
	# ※ `wp-env`コマンドは`root`ユーザーでコンテナを起動できないようになっているため。
	sudo gpasswd -a $(whoami) docker

	# 現在のディレクトリのパーミッションを変更
	sudo chown -R $(whoami):$(whoami) .

	# 使用するphpのバージョンをdocker-compose.ymlで指定したものに変更
	sudo update-alternatives --set php /usr/bin/php${PHP_VERSION}

	# PHP用のパッケージをインストール
	source .bin/install-php-packages.sh

	# intelephense用のインクルードファイルをインストール
	source .bin/install-intelephense-includes.sh

	# npmパッケージのインストール
	pnpm install
}


main
