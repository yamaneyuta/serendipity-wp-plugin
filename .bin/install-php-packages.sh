#!/bin/bash

# プロジェクトルートで実行されていることを確認
# カレントディレクトリに`package.json`が存在すれば、プロジェクトルートとみなす
if [ ! -f ./package.json ]; then
	echo "This script must be run in the root of the project" 1>&2
	exit 1
fi

function main() {
	# テスト用のcomposer.json及びcomposer.lockを削除
	delete_test_composer_files

	# phpunit実行に必要なパッケージをインストール
	install_phpunit

	# autoloadの設定を追加
	add_composer_autoload

	# includeディレクトリで扱うパッケージをインストール
	cd includes && composer install --ignore-platform-req=ext-gmp && cd -

	# 不要なファイルを削除
	delete_unnecessary_files
}

function delete_test_composer_files() {
	# テスト用のcomposer.json及びcomposer.lockを削除
	# ※ 開発環境のPHPバージョンが変更されている可能性があるため
	if [ -f tests/composer.json ]; then
		rm tests/composer.json
	fi
	if [ -f tests/composer.lock ]; then
		rm tests/composer.lock
	fi
}

function install_phpunit() {
	cd tests

	# `$PHP_VERSION`や`$WP_VERSION`によってPHPUnitのバージョンを変更
	# PHP7.4 + WP5.4～5.8 => ^7
	# PHP7.4 + WP5.9～6.6 => ^9
	# PHP8.0 + WP5.6～5.8 => ^7 => 実際は動作しない
	# PHP8.1 + WP5.9～6.6 => ^9
	#
	# 参考:
	# https://make.wordpress.org/core/handbook/references/phpunit-compatibility-and-wordpress-versions/
	PHP_UNIT_VERSION="9.6.21"
	if [ $PHP_VERSION = "7.4" ]; then
		if [ $WP_VERSION = "5.4" ] || [ $WP_VERSION = "5.5" ] || [ $WP_VERSION = "5.6" ] || [ $WP_VERSION = "5.7" ] || [ $WP_VERSION = "5.8" ]; then
			PHP_UNIT_VERSION="^7.5.20"
		fi
	elif [ $PHP_VERSION = "8.0" ]; then
		if [ $WP_VERSION = "5.6" ] || [ $WP_VERSION = "5.7" ] || [ $WP_VERSION = "5.8" ]; then
			# マトリクス上ではPHPUnit^7をサポートしているように見えるが、PHPUnit自体がPHP^7.1のサポートのためPHP8.0では動作しない
			echo "[E39CB887] PHPUnit7 does not support PHP8.0" 1>&2
			exit 1
		fi
	fi

	composer require --dev "phpunit/phpunit:${PHP_UNIT_VERSION}" "yoast/wp-test-utils:*" "yoast/phpunit-polyfills:*"

	cd -
}

function add_composer_autoload() {
	cd tests

	# `composer.json`にautoloadの設定を追加
	if [ -f ./composer.json ]; then
		# `autoload`セクションが存在しない場合は追加
		if ! grep -q '"autoload":' composer.json; then
			jq --tab '(.autoload //= {}) | (.autoload["psr-4"] //= {}) | .autoload["psr-4"]["Cornix\\Serendipity\\Test\\"] = "TestLib/" | .autoload["psr-4"]["Cornix\\Serendipity\\TestCase\\"] = "classes/"' composer.json > composer_tmp.json && mv composer_tmp.json composer.json
		fi

		# `composer.json`の内容を更新
		composer dump-autoload --optimize
	else
		echo "[3CA074DC] composer.json not found" 1>&2
		exit 1
	fi

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
