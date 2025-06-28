#!/bin/bash
# devcontainer.json#postStartCommand で指定されたスクリプト

# プロジェクトルートはこのスクリプトがあるディレクトリの親ディレクトリとする
PROJECT_ROOT="$(dirname "$(dirname "$0")")"
WP_HOST="http://localhost:8889"


create_docker_network() {
    # DOCKER_NETWORK_NAME 環境変数が設定されていない場合はエラー
    if [ -z "$DOCKER_NETWORK_NAME" ]; then
        echo "❌ [3107A203] DOCKER_NETWORK_NAME is not set."
        exit 1
    fi

    # Docker ネットワークを作成
    if ! docker network ls | grep -q "$DOCKER_NETWORK_NAME"; then
        docker network create "$DOCKER_NETWORK_NAME"
        if [ $? -eq 0 ]; then
            echo "✅ [1C13DD20] $DOCKER_NETWORK_NAME network created."
        else
            echo "❌ [678F6369] Failed to create $DOCKER_NETWORK_NAME"
            exit 1
        fi
    else
        echo "🔸 [8F22D465] Docker network '$DOCKER_NETWORK_NAME' already exists."
    fi
}

connect_network() {
    local container_ids=$(docker ps -aq)

    for container_id in $container_ids; do
        local container_name=$(docker inspect --format '{{.Name}}' "$container_id" | sed 's/^\///') # 名前から先頭のスラッシュを削除

        if docker inspect "$container_id" | jq -e ".[]?.NetworkSettings.Networks.${DOCKER_NETWORK_NAME}" >/dev/null 2>&1; then
            echo "🔸 [DAEEA927] $container_name ($container_id) is already connected to $DOCKER_NETWORK_NAME network."
        else
            docker network connect "$DOCKER_NETWORK_NAME" "$container_id"
            if [ $? -eq 0 ]; then
                echo "✅ [C588368B] Connected $container_name ($container_id) to $DOCKER_NETWORK_NAME network."
            else
                echo "❌ [239F22E9] Failed to connect $container_name ($container_id) to $DOCKER_NETWORK_NAME network."
                exit 1
            fi
        fi
    done
}

start_wp_env() {
    # apps/wp-plugin ディレクトリに移動して wp-env を起動
    # ※ ディレクトリ移動を行わない場合、作成されるコンテナ名が違うものになり、
    #    wp-pluginのPHPテストが実施できないため
    cd "$PROJECT_ROOT/apps/wp-plugin"
    
    npm run wp-env:start
    echo "✅ [00E10F59] wp-env started."

    cd -
}

# 他の環境（プライベートネットワークなど）を起動します
start_other_envs() {

    cd "$PROJECT_ROOT/infra"

    # 開発環境、CI共通で使用するDocker Composeファイルを起動
    docker compose -f compose.test.yml up -d
    echo "✅ [8759F6AD] compose.test.yml started."

    # DEVCONTAINER 環境変数が設定されている場合はcompose.dev.ymlも起動
    if [ "$DEVCONTAINER" = "true" ]; then
        docker compose -f compose.dev.yml up -d
        echo "✅ [F34636CC] compose.dev.yml started."
    fi
    
    cd -
}

wait_http_server() {
    local host="$1"
    MAX_RETRIES=120
    # リトライ間隔（秒）
    SLEEP_INTERVAL=1

    for ((i=1; i<=MAX_RETRIES; i++)); do
    # curl で HTTP ステータスコードを確認
    status=$(curl -s -o /dev/null -w "%{http_code}" "$host")
    if [[ "$status" == "200" ]]; then
        echo "✅ [14DC2901] HTTP server is ready! ($host)"
        return 0
    fi

    echo "⏳ [9C61509E] Attempt $i/$MAX_RETRIES: HTTP server not ready yet... (status: $status, host: $host)"
    sleep "$SLEEP_INTERVAL"
    done

    echo "❌ [97631AD9] HTTP server did not become ready in time."
    exit 1
}

main() {
    echo "🏗️ [23D0C564] Starting post-start script..."
    
    cd "$PROJECT_ROOT"

    # Dockerのネットワークを作成
    create_docker_network

    # 各Dockerを立ち上げ
    start_other_envs
    start_wp_env

    # WordPressが準備できるまで待機
    wait_http_server "$WP_HOST"

    # 作成したDockerのネットワークにwp-envで起動したコンテナを接続
    connect_network

    echo "🎉 [36013166] Post-start script completed successfully."
    echo "🚀 [19BF2196] Development environment is ready to go!"
}

main
