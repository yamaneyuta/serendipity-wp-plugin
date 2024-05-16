
# dockerコンテナの一覧を取得
docker_output=$(docker ps -a)

# `docker ps -a`の出力から、wp-cliコンテナのハッシュ値にあたる部分を抽出するスクリプト
# [hash]_default
awk_script='
/[0-9a-f]{32}-cli/ {
    match($0, /[0-9a-f]{32}-cli/)
    if (RSTART != 0) {
        print substr($0, RSTART, 32)
        exit
    }
}'

# dockerコンテナからハッシュ値を取得1
hash=$(echo "$docker_output" | awk "$awk_script")

# hashが32桁の16進数であることを確認
if [[ ! $hash =~ ^[0-9a-f]{32}$ ]]; then
	echo "Error: hash is not 32 hex digits"
	exit 1
fi


# `docker-compose.network.yml`ファイルを更新
docker_network_file_path=$(dirname $0)/docker-compose.network.yml
echo "version: '3'" > $docker_network_file_path
echo services: >> $docker_network_file_path
echo "  phpmyadmin:" >> $docker_network_file_path
echo "    networks:" >> $docker_network_file_path
echo "      - wpnetwork" >> $docker_network_file_path
echo "" >> $docker_network_file_path
echo "networks:" >> $docker_network_file_path
echo "  wpnetwork:" >> $docker_network_file_path
echo "    name: ${hash}_default" >> $docker_network_file_path
echo "    external: true" >> $docker_network_file_path
