#!/bin/bash
# devcontainer.json#postCreateCommand で指定されたスクリプト
# ディレクトリ移動があるため、bash -e で実行すること

# プロジェクトルートはこのスクリプトがあるディレクトリの親ディレクトリとする
PROJECT_ROOT="$(dirname "$(dirname "$0")")"

# プロジェクトが格納されるディレクトリ以下のパーミッションをログインユーザーに変更します
fix_permissions() {
    echo "🔑 [6A078A0F] Fixing permissions for current directory..."
    sudo chown -R "$(whoami):$(whoami)" "$PROJECT_ROOT"
    echo "✅ [8FC2C6C7] Permissions fixed successfully."
}

# NPMパッケージをインストールします
install_npm_packages() {
    echo "📦 [8E678580] Installing npm packages..."
    pnpm install --frozen-lockfile
    echo "✅ [2B4D55B0] NPM packages installed successfully."
}

install_php_packages() {
    echo "📦 [E1739CA3] Installing PHP packages..."
    cd "$PROJECT_ROOT/apps/wp-plugin"
    bash .bin/install-php-packages.sh
    bash .bin/install-intelephense-includes.sh
    echo "✅ [9A0F8FF5] PHP packages installed successfully."
    cd -
}

build() {
    # アプリケーションのビルドを実行
    echo "🏗️ [562FF5A7] Building application..."
    cd $PROJECT_ROOT
    npm run build
    echo "✅ [45DF7646] Application built successfully."
    cd -
}

# メイン関数
main() {
    echo "🚀 [268E63B5] Starting post-create script..."
    
    cd "$PROJECT_ROOT"

    fix_permissions
    install_npm_packages &
    NPM_INSTALL_PID=$!
    
    install_php_packages

    echo "⏳ [02A68ED0] Waiting for npm packages installation to complete..."
    wait $NPM_INSTALL_PID
    echo "✅ [D2331850] NPM packages installation completed."
    
    build

    echo "🎉 [43A01D28] Post-create script completed successfully."
    echo "🚀 [FDD7F9E0] Development environment is ready to go!"
}

# メイン関数を実行
main
