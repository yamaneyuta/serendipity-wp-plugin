#!/bin/bash

# プロジェクトルートで実行されていることを確認
# カレントディレクトリに`package.json`が存在すれば、プロジェクトルートとみなす
if [ ! -f ./package.json ]; then
	echo "This script must be run in the root of the project" 1>&2
	exit 1
fi

function main() {
	# intelephense用のインクルードファイルをインストール
	install_intelephense_includes
}


# intelephenseで参照エラーにならないよう、WordPressのテスト用ファイルをインストール
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


main
