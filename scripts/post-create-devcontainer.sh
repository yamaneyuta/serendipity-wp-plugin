#!/bin/bash
# devcontainer.json#postCreateCommand で指定されたスクリプト
# ディレクトリ移動があるため、bash -e で実行すること

# プロジェクトルートはこのスクリプトがあるディレクトリの親ディレクトリとする
PROJECT_ROOT="$(dirname "$(dirname "$0")")"

# プロジェクトが格納されるディレクトリ以下のパーミッションをログインユーザーに変更します
fix_permissions() {
    echo "[$(basename "$0")] Fixing permissions for current directory..."
    sudo chown -R "$(whoami):$(whoami)" "$PROJECT_ROOT"
}

# NPMパッケージをインストールします
install_npm_packages() {
    echo "[$(basename "$0")] Installing npm packages..."
    pnpm install --frozen-lockfile
}

# 使用するphpのバージョンをdocker-compose.ymlで指定したものに変更します
change_php_version() {
    echo "[$(basename "$0")] Changing PHP version to ${PHP_VERSION}..."
	sudo update-alternatives --set php /usr/bin/php${PHP_VERSION}
}

install_php_packages() {
    echo "[$(basename "$0")] Installing PHP packages..."
    cd "$PROJECT_ROOT/apps/wp-plugin"
    bash .bin/install-php-packages.sh
    bash .bin/install-intelephense-includes.sh
    cd -
}

build() {
    # アプリケーションのビルドを実行
    cd $PROJECT_ROOT
    npm run build
    cd -
}

# メイン関数
main() {
    echo "[$(basename "$0")] Starting post-create script..."
    
    cd "$PROJECT_ROOT"

    fix_permissions
    install_npm_packages
    change_php_version
    install_php_packages

    build

    echo "[$(basename "$0")] Post-create script completed successfully."
}

# メイン関数を実行
main
