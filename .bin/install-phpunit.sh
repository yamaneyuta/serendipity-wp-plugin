#!/bin/bash

# プロジェクトルートで実行されていることを確認
# カレントディレクトリに`package.json`が存在すれば、プロジェクトルートとみなす
if [ ! -f ./package.json ]; then
	echo "This script must be run in the root of the project" 1>&2
	exit 1
fi

mkdir -p .phpunit
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
