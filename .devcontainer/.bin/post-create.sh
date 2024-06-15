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

	# テスト用のcomposerパッケージをインストール
	install_phpunit_composer

	# intelephense用のインクルードファイルをインストール
	install_intelephense_includes

	# npmパッケージのインストール
	npm install
}


function install_intelephense_includes() {

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


function install_phpunit_composer() {
	cd .phpunit

	# PHPのバージョンが変更されている可能性があるため、ファイルをすべて削除
	rm -rf vendor/* composer.json composer.lock

	# WP_VERSIONによってPHPUnitのバージョンを変更
	# PHP7.4 + WP 5.4 ～ 5.8 => ^7
	# ここでは開発環境として使用するバージョンのみ記述
	if [ $WP_VERSION = "5.4" ] || [ $WP_VERSION = "5.5" ] || [ $WP_VERSION = "5.6" ] || [ $WP_VERSION = "5.7" ]; then
		PHP_UNIT_VERSION="^7.5.20"
	elif [ $WP_VERSION = "x.x" ]; then
		PHP_UNIT_VERSION="^9.5.2"
	else
		PHP_UNIT_VERSION="*"
	fi


	composer require --dev "phpunit/phpunit:${PHP_UNIT_VERSION}" "yoast/wp-test-utils:*" "yoast/phpunit-polyfills:*"

	cd -
}

main
