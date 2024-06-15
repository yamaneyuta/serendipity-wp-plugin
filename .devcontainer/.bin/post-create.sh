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
	join_docker_group

	# 現在のディレクトリのパーミッションを変更
	set_permissions

	# 使用するphpのバージョンをdocker-compose.ymlで指定したものに変更
	sudo update-alternatives --set php /usr/bin/php${PHP_VERSION}

	# intelephense用のインクルードファイルをインストール
	install_intelephense_includes

	# npmパッケージのインストール
	npm install
}

function join_docker_group() {
	sudo gpasswd -a $(whoami) docker
}

function set_permissions() {
	# ファイルのパーミッションを変更
	sudo chown -R $(whoami):$(whoami) .
}


function install_intelephense_includes() {
	# 変数WP_VERSIONにWordPressのバージョンを設定
	WP_VERSION=5.4.15

	INCLUDES_DIR=./.devcontainer/.intelephense-includes/wordpress-develop-${WP_VERSION}/tests/phpunit/includes
	# INCLUDES_DIRが存在する場合は処理抜け
	if [ -d $INCLUDES_DIR ]; then
		return
	fi

	# INCLUDES_DIRを作成し、WordPressのテスト用ファイルをダウンロード
	mkdir -p $INCLUDES_DIR
	curl -L https://github.com/WordPress/wordpress-develop/archive/refs/tags/${WP_VERSION}.tar.gz | tar zx -C /tmp
	mv /tmp/wordpress-develop-${WP_VERSION}/tests/phpunit/includes $INCLUDES_DIR
	rm -rf /tmp/wordpress-develop-${WP_VERSION}
}


main
