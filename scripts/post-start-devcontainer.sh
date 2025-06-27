#!/bin/bash
# devcontainer.json#postStartCommand で指定されたスクリプト

# プロジェクトルートはこのスクリプトがあるディレクトリの親ディレクトリとする
PROJECT_ROOT="$(dirname "$(dirname "$0")")"

echo "[$(basename "$0")] Starting WordPress environment..."

start_wp_env() {
    # apps/wp-plugin ディレクトリに移動して wp-env を起動
    # ※ ディレクトリ移動を行わない場合、作成されるコンテナ名が違うものになり、
    #    wp-pluginのPHPテストが実施できないため
    echo "[$(basename "$0")] Starting wp-env..."
    cd "$PROJECT_ROOT/apps/wp-plugin"
    npm run wp-env:start
    cd -
}

main() {
    echo "[$(basename "$0")] Starting post-start script..."
    
    cd "$PROJECT_ROOT"

    start_wp_env
    echo "[$(basename "$0")] Post-start script completed successfully."
}
