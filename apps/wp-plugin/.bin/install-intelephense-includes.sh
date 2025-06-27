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

	INCLUDES_DIR=./.intelephense/includes/wordpress-develop/tests/phpunit
	# INCLUDES_DIRを作成
	mkdir -p $INCLUDES_DIR
	# INCLUDES_DIR以下のファイルをすべて削除
	rm -rf ${INCLUDES_DIR}/*

	# WP_VERSIONにドット(.)が1つしか含まれていない場合は、最後に.0を追加
	# ※ WordPressのバージョン(tag)が`5.4`の場合、WordPress-developのバージョン(tag)は`5.4.0`となる
	if [ $(echo $WP_VERSION | grep -o "\." | wc -l) -eq 1 ]; then
		WP_DEV_VERSION="${WP_VERSION}.0"
	else
		WP_DEV_VERSION="${WP_VERSION}"
	fi

	# WordPressのテスト用ファイルを展開し、インクルードファイルをコピー
	curl -L https://github.com/WordPress/wordpress-develop/archive/refs/tags/${WP_DEV_VERSION}.tar.gz | tar zx -C /tmp
	mv /tmp/wordpress-develop-${WP_DEV_VERSION}/tests/phpunit/includes $INCLUDES_DIR
	rm -rf /tmp/wordpress-develop-${WP_DEV_VERSION}

	# ${INCLUDES_DIR}/includes/abstract-testcase.php 内の `PHPUnit_Framework_TestCase`を `PHPUnit\Framework\TestCase`に置換
	sed -i -e 's/PHPUnit_Framework_TestCase/PHPUnit\\Framework\\TestCase/g' ${INCLUDES_DIR}/includes/abstract-testcase.php
}


main
