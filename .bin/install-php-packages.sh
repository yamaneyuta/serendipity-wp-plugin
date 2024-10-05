#!/bin/bash

# プロジェクトルートで実行されていることを確認
# カレントディレクトリに`package.json`が存在すれば、プロジェクトルートとみなす
if [ ! -f ./package.json ]; then
	echo "This script must be run in the root of the project" 1>&2
	exit 1
fi

function main() {
	# phpunit実行に必要なパッケージをインストール
	install_phpunit

	# includeディレクトリで扱うパッケージをインストール
	cd includes && composer install --ignore-platform-req=ext-gmp && cd -

	# 不要なファイルを削除
	delete_unnecessary_files
}

function install_phpunit() {
	mkdir -p .phpunit
	cd .phpunit

	# PHPのバージョンが変更されている可能性があるため、ファイルをすべて削除
	rm -rf vendor/* composer.json composer.lock

	# `$PHP_VERSION`や`$WP_VERSION`によってPHPUnitのバージョンを変更
	# PHP7.4 + WP5.4～5.8 => ^7
	# PHP7.4 + WP5.9～6.6 => ^9
	# PHP8.1 + WP5.9～6.6 => ^9
	# ここでは開発環境として使用するバージョンのみ記述
	# 参考:
	# https://make.wordpress.org/core/handbook/references/phpunit-compatibility-and-wordpress-versions/
	PHP_UNIT_VERSION="9.6.21"
	if [ $PHP_VERSION = "7.4" ]; then
		if [ $WP_VERSION = "5.4" ] || [ $WP_VERSION = "5.5" ] || [ $WP_VERSION = "5.6" ] || [ $WP_VERSION = "5.7" ] || [ $WP_VERSION = "5.8" ]; then
			PHP_UNIT_VERSION="^7.5.20"
		fi
	fi

	composer require --dev "phpunit/phpunit:${PHP_UNIT_VERSION}" "yoast/wp-test-utils:*" "yoast/phpunit-polyfills:*"

	cd -
}

function delete_unnecessary_files() {

	# 削除対象のディレクトリ名及びファイル名(全パッケージが対象)
	delete_directory_names=("test" "tests" "examples" ".github")
	delete_file_names=("phpunit.xml.dist" "phpunit.xml.legacy" "phpunit.xml" "Makefile" "CHANGELOG.md" "UPGRADING.md" "UPGRADE.md")

	for dir in "${delete_directory_names[@]}"; do
		find includes/vendor -type d -name "$dir" -exec rm -rf {} +
	done
	for file in "${delete_file_names[@]}"; do
		find includes/vendor -type f -name "$file" -exec rm -rf {} +
	done

	# 特定のディレクトリを削除
	delete_directory_paths=(
		"includes/vendor/psr/http-message/docs"
		"includes/vendor/web3p/web3.php/docker"
		"includes/vendor/web3p/web3.php/scripts"
		"includes/vendor/webonyx/graphql-php/docs"
	)
	for path in "${delete_directory_paths[@]}"; do
		rm -rf $path
	done
}


main
